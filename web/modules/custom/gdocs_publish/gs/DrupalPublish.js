// #if false
/**
 * @file DrupalPublish.js
 *
 * Apps script to publish Google Doc content to Drupal. Requires Drupal module.
 *
 */
// #endif
// #define var VERSION=16
// #define function RELEASE_DATE() { return new Date().toDateString() }
// #include "header.js"

/**
 * Calls open hook and sets metadata
 */
function onInstall(e) {
  onOpen(e);
}

/**
 * Adds menu items.
 */
function onOpen(e) {
  DocumentApp.getUi()
   .createAddonMenu()
   .addItem('Publish...', 'publishToDrupal')
   .addSeparator()
   .addItem('Set Drupal Host Name', 'setHost')
   .addItem('About', 'showAbout')
   .addSeparator()
   .addItem('Display converted HTML', 'showHTML')
   .addItem('Show warnings', 'showWarnings')
   .addToUi();
 }

/**
 * Prompt user for hostname of Drupal site.
 */
function setHost() {
  var ui = DocumentApp.getUi();

  var response = ui.prompt(
      'Drupal Host Name ',
      'Enter the URL of the Drupal site (host name only, no path):',
      ui.ButtonSet.OK_CANCEL);

  var button = response.getSelectedButton();
  var host = response.getResponseText();

  if (button == ui.Button.OK) {
    if (host===null || !host.trim()) {
      ui.alert("Host name cannot be blank");
    }

    host.replace(/ /g,'');

    if (! /^[a-zA-Z0-9\/\._:-]+$/.test(host)) {
      ui.alert("That doesn't look like a valid host name.");
      return false;
    }

    if (! /^https?/.test(host)) {
      host = 'http://'+host;
    }

    if (! /\/$/.test(host)) {
      host+='/';
    }

    PropertiesService.getScriptProperties().setProperty('DRUPAL_HOST', host);
  }
}

/**
 * Display basic "About" message
 *
 * Note that if this script is excecuted prior to installation, the
 * script properties will not be set, so we display "Development Version"
 * and the current date instead.
 */
function showAbout() {
  var GDP_VERSION = // #put '"' + VERSION + '";'
  var GDP_DATE = // #put '"' + RELEASE_DATE() + '";'

  var ui = DocumentApp.getUi();
  var host = getDrupalHostName();
  if (host) {
    var about = '<p>Version ' + GDP_VERSION;
    about += ', ' + GDP_DATE + '</p>';
    about += '<p><a href="mailto:ken@harmoni.ca">Ken Ficara</a></p>'
    about += '<p><a href="'+host+'gdp" target="_blank">More information</a></p>'
// #ifdef DEBUG
    about += '<p><b>Debug logging enabled</b></p>';
// #else
    about += '<p>Debug logging disabled</p>';
// #endif
    var html = HtmlService
     .createHtmlOutput(about)
     .setWidth(250)
     .setHeight(300);
    ui.showModelessDialog(html, 'DrupalPublish');
  }
}

/**
 * Check if host name is set and alert if not.
 *
 * @return Boolean|String host if set, false if not.
 */
function getDrupalHostName() {
  var P = PropertiesService.getScriptProperties().getProperties();

  if (P.hasOwnProperty('DRUPAL_HOST')) {
    var host = PropertiesService.getScriptProperties().getProperty('DRUPAL_HOST');

    if (host!==null && host.trim()) {
      return host;
    }
  }

  DocumentApp.getUi().alert("Please set the Drupal host name.");
  return false;
}


/**
 * Display the publish dialog
 */
function publishToDrupal() {
  var ui = DocumentApp.getUi();
  var host = getDrupalHostName();

  if (host) {
    var doc = DocumentApp.getActiveDocument();
    var dialogTemplate = HtmlService.createTemplateFromFile('DrupalPublishForm');
    dialogTemplate.host=host;
    dialogTemplate.endpoint=host+'gdp/publish';
    dialogTemplate.title=doc.getName();
    dialogTemplate.user=Session.getActiveUser().getEmail();
    dialogTemplate.id=DocumentApp.getActiveDocument().getId();
    ui.showModalDialog(dialogTemplate.evaluate(), 'Publish To Drupal');
  }
}

/**
 * Convert document to HTML
 *
 * Diagnostic function that creates the HTML but shows it in a popup
 * rather than publishing it to Drupal.
 */
function showHTML() {
// #ifdef DEBUG
  setLogLevel(1);
  logMsg('Starting showHTML');
// #endif
  var content = createHTML();

  content = content.replace(/\</g,'&lt;');
  content = content.replace(/\>/g,'&gt;');
  content = '<pre>'+content+'</pre>';

  var output = HtmlService
     .createHtmlOutput(content)
     .setWidth(800)

  DocumentApp.getUi().showModelessDialog(output, 'Converted HTML');
}

/**
 * Display warning messages
 *
 * Since they may be irrelevant, and since they don't fit in an alert
 * box, we only alert users that there are warnings after processing,
 * and leave their actual display to a separate function.
 *
 */
function showWarnings() {
  var ui = DocumentApp.getUi();
  var warnings = PropertiesService.getDocumentProperties().getProperty('GDP_WARNINGS');
  if (warnings) {
    var html = HtmlService
    .createHtmlOutput('<h3>Parts of your document may have been skipped or not fully processed:</h3>'+warnings)
    .setWidth(600)
    ui.showModelessDialog(html, 'Warnings');
  }
  else {
    ui.alert("All is well!");
  }
}
/**
 * Convert document content to HTML
 *
 * This function is called by the publish or convert menu items. It
 * is merely a wrapper around the recursive processing calls.
 *
 */
function createHTML() {
  ui = DocumentApp.getUi();

  // Global stack to hold list items
 var T = new Tags;

  try {
    processElement(T, DocumentApp.getActiveDocument().getBody());
  }
  catch (e) {
    ui.alert (e);
    return false;
  }

  /* If there are any warnings, alert the user but don't display them since they're
   * probably long, and we can't put up a modeless dialog here. Store them for later use.
   */
  if (T.warnings()) {
    ui.alert('There may be some problems. See DrupalPublish->Show Warnings.');
    PropertiesService.getDocumentProperties().setProperty('GDP_WARNINGS', T.getWarnings());
  }

  return T.clear();
}

/**
 * Recursively process document elements
 *
 * This processes only a small number of the many elements comprising
 * a Google Document.
 *
 * @param {object} T - a Tags object
 * @param {object} E - a document element
 * @return {String} HTML content
 */
function processElement (T, E) {

// #ifdef DEBUG
  logMsg('---------------------------------');
  logMsg("processing element: "+E);
  dumpElementText(E);

// #endif
  var elementType = E.getType();
  switch (elementType) {
    case DocumentApp.ElementType.PARAGRAPH:
      if (E.getText().length == 0) {
// #ifdef DEBUG
        logMsg("Ignoring empty paragraph");
// #endif
        return '';
      }
      var heading = E.getHeading();
      switch (E.getHeading()) {
        case DocumentApp.ParagraphHeading.NORMAL:
           T.open('p');
          break;
        case DocumentApp.ParagraphHeading.TITLE:
// #ifdef DEBUG
          logMsg("Title: " + E.getText());
// #endif
          T.open('p', {'class':'gd.title'});
          break;
        case DocumentApp.ParagraphHeading.SUBTITLE:
// #ifdef DEBUG
          logMsg("Subtiitle: " + E.getText());
// #endif
          T.open('p', {'class':'gd.subtitle'});
          break;
        default:
          var h = String(heading).match(/([1-6])$/);
// #ifdef DEBUG
          logMsg("headline " + h[0]);
// #endif
          T.open('h'+h[0]);
      }

      if (E.getIndentStart()) {
        T.open('blockquote');
      }

      if (E.getNumChildren() == 0) {
        T.discard();
      }

      break;

    case DocumentApp.ElementType.TEXT:
      processTextElement(T, E);
      break;

    case DocumentApp.ElementType.LIST_ITEM:
      processListElement(T,E);
      break;

    case DocumentApp.ElementType.HORIZONTAL_RULE:
      T.open('hr');
      break;

    case DocumentApp.ElementType.TABLE:
      T.warn ("Tables are not supported. Each cell was turned into a separate paragraph.");
      break;

    case DocumentApp.ElementType.EQUATION:
      T.warn ("Equations are not supported. They will be converted to text, and will probably contain errors.");
      break;

    case DocumentApp.ElementType.FOOTNOTE:
      T.warn ("Skipped footnote (not supported).");
      break;

    case DocumentApp.ElementType.INLINE_IMAGE:
    case DocumentApp.ElementType.INLINE_DRAWING:
      T.warn ("Skipped image or drawing (not supported).");
      break;

    case DocumentApp.ElementType.TABLE_OF_CONTENTS:
      T.warn ("Skipped table of contents (not supported).");
      break;

    default:
      logMsg ("Unsupported element type ignored: " + E.getType());
  }

  // Recurse, unless we're working on a list item
  if (elementType != "LIST_ITEM") {
    if (typeof E.getChild === 'function') {
      for (var i=0 ; i <  E.getNumChildren() ; i++) {
// #ifdef DEBUG
        logMsg("Processing child "+i + ' of ' + E);
// #endif
        processElement(T, E.getChild(i));
      }
    }
    else {
// #ifdef DEBUG
      logMsg('Closing all at end of processElement');
// #endif
      T.closeAll();
    }
  }
}


/**
 * Process a text element
 *
 * Since text elements are (sigh) not trees, this loops rather than recursing.
 *
 * @function processTextElement
 * @param {object} T - a Tags stack
 * @param {object} X - a TextItem document element
 */
function processTextElement (T, X) {

  var indices = X.getTextAttributeIndices();

  for (var i=0; i < indices.length; i ++) {
    var atts = X.getAttributes(indices[i]);
    var startPos = indices[i];
    var endPos = i+1 < indices.length ? indices[i+1]: X.length;
    var text = X.getText().substring(startPos, endPos);

    if (typeof(text) !== 'undefined') {
// #ifdef DEBUG
      logMsg('TEXT: starting text part ' + i + ': '+ text);
// #endif
      if (atts.ITALIC) {
        T.open('em');
      }
      if (atts.BOLD) {
        T.open('strong');
      }
// Ignoring this, since every link element comes with a free underline style.
//       if (atts.UNDERLINE) {
//         T.open('u');
//       }
      if (atts.STRIKETHROUGH) {
        T.open('strike');
      }
      if (atts.LINK_URL) {
        T.open ('a', {'href':atts.LINK_URL});
      }

      T.content(text);

      // If we opened any tags above, close them.
      var C = T.current();
      while (C.tag == 'em' || C.tag == 'strong' || C.tag == 'strike' || C.tag == 'a') {
        T.close();
        C = T.current();
      }
// #ifdef DEBUG

      logMsg('TEXT: finishing text part ' + i );
// #endif
    }
    else {
// #ifdef DEBUG
      logMsg ("TEXT: Undefined text element");
// #endif
    }
  }
}

/**
 * Separate recursive function for list items
 *
 * @param {object} T - a Tags stack
 * @param {object} L - a ListItem document element
 */
function processListElement(T,L) {

  // Ignore all the nonsensical bullet types. If the glyph is
  // any type of bullet, the list is unordered. Otherwise it's ordered.
  if (/BULLET/.test(L.getGlyphType())) {
    var listTag='ul';
  }
  else {
    var listTag='ol';
  }

  var listID = L.getListId();
  var listLevel = L.getNestingLevel();
  var lastList = T.last(['ul','ol']);

// #ifdef DEBUG
  logMsg('LIST: Processing list ' + listID + '(' + listTag + ')' + ' at level ' + listLevel);

// #endif
  if (lastList) {
    // We have a currently open list
// #ifdef DEBUG
    logMsg('LIST: There is an open list ' + lastList.attributes.id + ' (Level '+ lastList.attributes['data-level'] + ')');
// #endif

    if (listLevel == lastList.attributes['data-level']) {
      // This is another item at the same level for that list
// #ifdef DEBUG
      logMsg('LIST: New item at same level ' + listLevel);
// #endif
    }
    else if (listLevel > lastList.attributes['data-level']) {
      // This item belongs to a new nested list at a deeper level
// #ifdef DEBUG
      logMsg('LIST: New nested list at ' + listLevel);
// #endif
      T.open (listTag, {
        'id' : listID + "-" + listLevel,
        'data-level' : listLevel
      });
    }
    else {
      // The list at current level is done, and we're back to the parent level
      // (or possibly a higher level)
      while (lastList.attributes['data-level'] > listLevel) {
        T.close();
        lastList = T.last(['ul','ol']);
// #ifdef DEBUG
        logMsg('LIST: Closing nested list at ' + lastList.attributes['data-level'] + ' now at level ' + listLevel);
// #endif
      }
    }
  }
  else {
    // This is the first item for a new list.
// #ifdef DEBUG
    logMsg('LIST: New list ' + listID + ' at ' + listLevel);
// #endif
    T.open (listTag, {
      'id' : listID + "-" + listLevel,
      'data-level' : listLevel
    });
  }

  // Now that we're in the proper context, open tag for the list item itself
  T.open('li', {
    'class':'LID-'+listID+"-"+listLevel,
    'data-level': listLevel
  });

  // Process the content of the list item. We ignore everything
  // except for text items and other list items.
  for (var i=0 ; i <  L.getNumChildren() ; i++) {
    var child = L.getChild(i);
    var type = child.getType();
    if (type == DocumentApp.ElementType.TEXT) {
// #ifdef DEBUG
      logMsg('LIST: Processing text item at level ' + listLevel);
// #endif
      processTextElement(T, child);
    }
    else if (type == DocumentApp.ElementType.LIST_ITEM) {
// #ifdef DEBUG
      logMsg('LIST: Processing list item at level ' + listLevel);
// #endif
      processListElement(T, child);
    }
  }

  // Close the list item
// #ifdef DEBUG
  logMsg('LIST: Closing list item: ' + T.current().tag);
// #endif
  T.close();
// #ifdef DEBUG
  logMsg('LIST: closed ');
// #endif

  if (L.isAtDocumentEnd()) {
// #ifdef DEBUG
    logMsg('LIST: End of document; closing all lists');
// #endif
    T.closeAll();
  }
  else {
    if (L.getNextSibling().getType() != DocumentApp.ElementType.LIST_ITEM) {
// #ifdef DEBUG
      logMsg('LIST: Next item is not a list, close to ' + listTag);
// #endif
      T.closeTo(listTag);
    }
  }
// #ifdef DEBUG
  logMsg('LIST: Bottom of list loop.');
// #endif

}

/**
 * Diagnostic function
 */
function dumpElementAttributes (E) {
  if(typeof E.getAttributes === 'function') {
    atts = E.getAttributes();
    for (var a in atts) {
      logMsg(a + ":" + atts[a]);
    }
  }
  else {
    logMsg("Cannot dump attributes");
  }
}

/**
 * Diagnostic function
 */
function dumpElementText (E) {
    if(typeof E.getText === 'function') {
      var text=E.getText();
      logMsg('text length:' + text.length);
      logMsg('text dump:'+text.substring(0, 20));
    }
    else {
      logMsg("Cannot dump text");
    }
}

/**
 * Diagnostic function
 */
function dumpObject (O) {
    for (var k in O) {
      logMsg(k +":"+O[k]);
    }
}

/**
 * Set log level
 *
 * @param {Number} log level
 */
function setLogLevel(level) {
  PropertiesService.getDocumentProperties().setProperty('GDP_LOG_LEVEL', level);
}

/**
 * Diagnostic logging function
 *
 * @param {string} msg - The message to log
 * @param {Boolean} verbose - whether to do anything
 */
function logMsg(msg) {
  var level = PropertiesService.getDocumentProperties().getProperty('GDP_LOG_LEVEL');
  if (level) {
    Logger.log(msg);
  }
}


// ------------------------------------------------------------------
// Tags.js
//
//  Defines a class that uses a stack to keep track of open tags.
// ------------------------------------------------------------------

// #include "Tags.js"
