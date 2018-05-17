/**
 * @file DrupalPublish.gs
 * @version 16
 *
 * Released: Sun Jan 22 2017
 *
 * Debug logging: OFF
 *
 * ******************************************************************
 * **************** DO NOT EDIT THIS FILE DIRECTLY ******************
 * ******************************************************************
 * **                                                              **
 * ** The code for this add-on is maintained along with the module **
 * ** code. Changes to this code will be overwritten on the next   **
 * ** update. See the README for instructions on compiling.        **
 * ******************************************************************
 *
 * @copyright 2016 Harmonica, LLC
 * @author Ken Ficara <ken@harmoni.ca>
 *
 * DrupalPublish is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

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
  var GDP_VERSION = "16";
  var GDP_DATE = "Sun Jan 22 2017";

  var ui = DocumentApp.getUi();
  var host = getDrupalHostName();
  if (host) {
    var about = '<p>Version ' + GDP_VERSION;
    about += ', ' + GDP_DATE + '</p>';
    about += '<p><a href="mailto:ken@harmoni.ca">Ken Ficara</a></p>'
    about += '<p><a href="'+host+'gdp" target="_blank">More information</a></p>'
    about += '<p>Debug logging disabled</p>';
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

  var elementType = E.getType();
  switch (elementType) {
    case DocumentApp.ElementType.PARAGRAPH:
      if (E.getText().length == 0) {
        return '';
      }
      var heading = E.getHeading();
      switch (E.getHeading()) {
        case DocumentApp.ParagraphHeading.NORMAL:
           T.open('p');
          break;
        case DocumentApp.ParagraphHeading.TITLE:
          T.open('p', {'class':'gd.title'});
          break;
        case DocumentApp.ParagraphHeading.SUBTITLE:
          T.open('p', {'class':'gd.subtitle'});
          break;
        default:
          var h = String(heading).match(/([1-6])$/);
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
        processElement(T, E.getChild(i));
      }
    }
    else {
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
    }
    else {
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

  if (lastList) {
    // We have a currently open list

    if (listLevel == lastList.attributes['data-level']) {
      // This is another item at the same level for that list
    }
    else if (listLevel > lastList.attributes['data-level']) {
      // This item belongs to a new nested list at a deeper level
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
      }
    }
  }
  else {
    // This is the first item for a new list.
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
      processTextElement(T, child);
    }
    else if (type == DocumentApp.ElementType.LIST_ITEM) {
      processListElement(T, child);
    }
  }

  // Close the list item
  T.close();

  if (L.isAtDocumentEnd()) {
    T.closeAll();
  }
  else {
    if (L.getNextSibling().getType() != DocumentApp.ElementType.LIST_ITEM) {
      T.closeTo(listTag);
    }
  }

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

/**
 * Represents a set of HTML tags
 *
 * @class Tags
 */
var Tags = function(){
  this.stack = [];
  this.stack.push ({
    'tag' : '__parent',
    'content' : ''
  });

  /**
   * Object to store indexes keyed by tag type
   */
  this.index={};

  /**
   * Array of warning messages generated during processing
   */
  this.warningMessages=[];

  /**
   * To store lists of tags needing special processing.
   *
   * @property
   * @private
   */
  this.tagtypes={};
  /**
   * Tags after which we should not insert a newline.
   *
   * @todo Should be configurable.
   * @property
   * @private
   */
  this.tagtypes.inline = [
    'a',
    'b',
    'em',
    'i',
    'strike',
    'strong',
    'u'
  ];

  /**
   * Tags that do not require closing tags.
   *
   * @property
   * @private
   */
  this.tagtypes.empty = [
    'br',
    'diagnostic',
    'hr',
  ];

};

/**
 * Open a new tag.
 *
 * @method open
 *
 * @param {string} tag - (without angle brackets)
 * @param {object} [attributes] - optional object with attribute:value items
 *
 * @returns {string} opening tag value
 */
Tags.prototype.open = function (tag, attributes) {
  var prefix = this.isInline(tag) ? '' : "\n";

  var top = this.stack.push({
    'tag' : tag,
    'attributes': attributes,
    'content' : prefix + '<' + tag
  });

  top -= 1;

  this.stack[top].content += this.renderAttributes(attributes);

  // Handle self-closing tags
  if (this.isSelfClosing(tag)) {
    var emptyTag = this.stack.pop();
    this.stack[this.stack.length-1].content += emptyTag.content;
    this.stack[this.stack.length-1].content += " />\n";
  }
  else {
    this.stack[top].content += ">";

    // this.index is an array of arrays indexing the open tags
    if (this.index[tag] === undefined ) {
      this.index[tag] = [];
    }
    this.index[tag].push(top);
  }
}

/**
 * Produce string of attribute="value" items from object
 *
 * @param {object} attributesA - an attributes (NOT tags) object
 * @return {string} formatted attribute string
 */
Tags.prototype.renderAttributes = function (attributes) {
  var output = '';
  if (typeof(attributes) == 'object') {
    for (var a in attributes) {
      output += ' ' + a + '="' + attributes[a] + '"';
    }
  }
  return output;
}

/**
 * Add content to currently open tag.
 *
 * @method content
 * @param {String} content - HTML content to add
 */
Tags.prototype.content = function (content) {
  this.stack[this.stack.length-1].content += content;
}

/**
 * Close last opened tag
 *
 * @method close
 *
 * @returns {string} the closed tag
 */
Tags.prototype.close = function () {
  var closed = this.stack.pop();
  this.stack[this.stack.length-1].content += closed.content + '</' + closed.tag + ">";

  if ( ! this.isInline(closed.tag)) {
    this.stack[this.stack.length-1].content += "\n";
  }

  this.index[closed.tag].pop();
  return closed;
}

/**
 * Tests whether tag is on the list of inline tags
 *
 * @param {string} tag - the tag to test
 * @return {Boolean} true if tag is inline
 */
Tags.prototype.isInline = function (tag) {
  for (var t in this.tagtypes.inline) {
    if (this.tagtypes.inline[t] == tag) {
      return true;
    }
  }
  return false;
}

/**
 * Tests whether tag is on the list of self-closing tags
 *
 * @param {string} tag - the tag to test
 * @return {Boolean} true if tag is self-closing
 */
Tags.prototype.isSelfClosing = function (tag) {
  for (var t in this.tagtypes.empty) {
    if (this.tagtypes.empty[t] == tag) {
      return true;
    }
  }
  return false;
}

/**
 * Discard last opened tag
 *
 * @method discard
 *
 * @returns {string} the discarded tag
 */
Tags.prototype.discard = function () {
  var closed = this.stack.pop();
  this.index[closed.tag].pop();
  return closed;
}

/**
 * Tells how many tags are currently open
 *
 * @method depth
 *
 * @return {Number} depth of stack
 */
Tags.prototype.depth = function () {
  return this.stack.length;
}

/**
 * Return (but do not close) the currently open tag
 *
 * @method current
 * @return {Object} the currently open tag
 */
Tags.prototype.current = function () {
  return this.stack[this.stack.length-1];
}

/**
 * Close all open tags at once, except for the parent tag,
 * leaving all content in the parent tag.
 *
 * @method closeAll
 */
Tags.prototype.closeAll = function () {
  var closed;
  while (this.stack.length > 1) {
    closed = this.close();
  }
  return closed;
}

/**
 * Close open tags up to the tag named in its argument
 *
 * Closes tags up to and including the most recent occurence
 * of the tag named in its argument.
 *
 * @method closeTo
 * @param {String} tag - tag to stop at
 * @returns {Object} the last tag closed
 */
Tags.prototype.closeTo = function (tag) {
  var last;
  while (this.stack.length > 1) {
    last = this.close();
    if (last.tag == tag) {
      return last;
    }
  }
}

/**
 * Clear the stack and return the content
 *
 * @method clear
 *
 * @return {string} HTML content
 */
Tags.prototype.clear = function () {
  this.closeAll();
  var content = this.stack[0].content;
  this.stack[0].content = '';
  return content;
}

/**
 * Find and return the last open tag of a given type
 *
 * @method last
 * @param {String|Array} tag or tags to look for (leave blank for any)
 * @return {Object|Boolean} The tag, or false if none is open
 */
Tags.prototype.last = function(tag) {
  // If tag isn't passed at all, just return the top item on the stack.
  if (!tag) {
    if (this.stack.length > 0) {
      return this.stack[this.stack.length-1];
    }
    else {
      return false;
    }
  }

  // If tag is a string, return last occurrence of that tag.
  else if (typeof(tag)=='string') {
    var L = this.findLast(tag);
    return L ? this.stack[L] : false;
  }

  // If tag is an array, return last occurrence of any of the tags
  else if (Array.isArray(tag)) {
    var last = -1;
    for (var t in tag) {
      var L = this.findLast(tag[t]);
      if (L && L > last) {
       last=L;
      }
    }
    return (last > -1) ? this.stack[last] : false ;
  }

  // If tag is something else ignore it.
  else {
    return false;
  }
}


/**
 * Return index of last occurence of tag
 *
 * @function findLast
 * @private
 * @param {string} tag - tag to look for, no brackets
 * @return {number|Boolean} index of the tag in the array, or false
 */
Tags.prototype.findLast = function (tag) {
  if (typeof (this.index[tag]) !== 'undefined') {
    var L = this.index[tag].length;
    return (L > 0) ? this.index[tag][L-1] : false;
  }
  else {
    return false;
  }
}

/**
 * Inserts a dummy <diagnostic /> tag into the output
 *
 * @param {string} msg - Diagnostic message
 */
Tags.prototype.diagnostic = function (msg) {
  var C = this.current();
  this.open('diagnostic', {
    'message': msg,
    'current': C.tag
  });
}

/**
 * Registers a warning message
 *
 * @method
 * @param {String} msg - the warning message
 *
 */
Tags.prototype.warn = function (msg) {
  var C = this.current();
  var attributes = this.renderAttributes(C.attributes);
  this.warningMessages.push({
    'msg': msg,
    'tag' : C.tag,
    'attributes' : attributes
  });
}

/**
 * Check if any warnings were raised
 *
 * @method
 * @return {Boolean}
 */
Tags.prototype.warnings = function () {
  return this.warningMessages.length ? true : false;
}

/**
 * Get a single block of formatted text showing the warnings
 *
 * @method
 * @return {String} a multiline string of formatted warnings
 */
Tags.prototype.getWarnings = function () {
  var output = '';
  for (i in this.warningMessages) {
    w = this.warningMessages[i];
    output += w.msg + "\n";
    if (w.tag != '__parent') {
      output += "\t in tag " + w.tag;
    }
    if (w.attributes) {
      output += ' (' + w.attributes.trim() + ')';
    }
  output += "\n";
  }

  return output;
}

/**
 * Diagnostic routine to print stack contents
 *
 * @method dump
 * @param {String} label - displayed in first line of dump
 * @return {String} Multiline string showing stack contents
 */
Tags.prototype.dump = function (label) {
  var output = "----------\nDUMP: " + label + "\n";

  function dumpItem(item, index) {
    output += index + ": ";
    output += item.tag + "\n";
    if (typeof(item.attributes) == 'object') {
      for (var a in item.attributes) {
        output += ' ' + a + '="' + item.attributes[a] + '"' + "\n";
      }
    }
    else {
      output += "\n";
    }
    output += "\n";
  }

  this.stack.forEach(dumpItem);
  return output;
}
