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
