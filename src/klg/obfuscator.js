(function(obfuscator) {
  "use strict";

  /**
   * Decrypt raw block of data with XXTEA algorithm.
   * @param {Array.<number>} data Block of data
   * @param {Array.<number>} key  Encryption key
   * @return {Array.<number>} Decrypted block
   **/
  var xxtea_dec = function(data, key) {
    var z, y = data[0], e, DELTA = 0x9e3779b9;
    var p, q = 0 | (6 + 52/data.length), sum = q*DELTA;
    while (sum > 0) {
      e = sum >>> 2;
      for (p = data.length-1; p >= 0; p--) {
        z = data[(data.length+p-1)%data.length];
        data[p] -= ((z>>>5)^(y<<2)) + ((y>>>3)^(z<<4)) ^ (sum^y) + (key[(p^e)&3]^z);
        y = data[p] &= 0xffffffff;
      }
      sum -= DELTA;
    }
    return data;
  };

  /**
   * Decode Base64url to array of 32-bit words
   * @param {string} str Base64url encoded string
   * @return {Array.<number>} Block of 32-bit words
   * @protected
   **/
  var base64url_dec = (function(alpha) {
    return function(str) {
      var a32 = [], a8 = [];
      for (var i = 0; i < str.length; i += 4) {
        var a = str.charCodeAt(i),
            b = str.charCodeAt(i+1),
            c = str.charCodeAt(i+2),
            d = str.charCodeAt(i+3);
        a8.push((0x0 | alpha[a]) << 2 | (0 | alpha[b]) >>> 4);
        a8.push((0xf & alpha[b]) << 4 | (0 | alpha[c]) >>> 2);
        a8.push((0x3 & alpha[c]) << 6 | (0 | alpha[d]));
        if (a8.length >= 4) {
          a = 0;
          for (var j = 0; j < 4; j++) {
            a <<= 8;
            a |= a8.shift();
          }
          a32.push(a);
        }
      }
      return a32;
    };
  })((function() {
    var a = [];
    var s = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_";
    for (var i = 0; i < s.length; i++)
      a[s.charCodeAt(i)] = i;
    return a;
  })());

  /**
   * Unpack decrypted data to binary string
   * @param {Array.<number>} a Block of 32-bit words
   * @return {string} Decoded binary string
   * @protected
   **/
  var strunpack = function(a) {
    var i, s = "";
    for (i = 1; i < a.length; i++)
      s += String.fromCharCode(
          (a[i]>>>24) & 0xff, (a[i]>>>16) & 0xff,
          (a[i]>>> 8) & 0xff, (a[i]>>> 0) & 0xff);
    i = s.length-1;
    while (i >= 0 && !s.charCodeAt(i)) --i;
    return s.substring(0, i+1);
  };

  /**
   * Decrypt cryptogram string to UTF-8 plain text
   * @param {string} xstr Cryptogram
   * @param {Array.<number>} key 128-bit decryption key
   * @return {string} Plain text
   * @protected
   **/
  var decode = function(xstr, key) {
    var str = strunpack(xxtea_dec(base64url_dec(xstr), key));
    return decodeURIComponent(escape(str));
  };

  /**
   * Match regular expresion and reutrn capture or
   * empty string (falsey) if anything goes wrong.
   * @param {string} str Source string
   * @param {string} re Regular expression
   * @return {string}
   **/
  var mrx = function(str, re) {
    return (String(str).match(re) || [0,""])[1];
  };

  /**
   * Default key.
   * @type {?Array.<number>}
   **/
  obfuscator["key"] = null;

  /**
   * De-obfuscate string.
   * Throws URIError if decrypted string is not valid
   * UTF-8 stream (likely because wrong decryption key).
   * If key is left undefined, "key" property will be used.
   * @param {string} msg Encrypted message
   * @param {Array.<number>=} key 128-bit decryption key
   * @return {string} Decrypted message
   **/
  obfuscator["decode"] = function(msg, key) {
    if (!key) key = this.key;
    return decode(msg, key);
  };

  /**
   * Decrypt href attribute from xhref data
   * attribute, title, name, or link path.
   * Uses the key property of obfuscator object.
   * @param {Object} a  An anchor element to operate on
   **/
  obfuscator["href"] = function(a) {
    if (!a.getAttribute("data-decrypted")) {
      var e = a.dataset && a.dataset.xhref;
      e = e || a.getAttribute("data-xhref");
      e = e || mrx(a.title, "^([0-9A-Za-z_-]+)$");
      e = e || mrx(a.name, "^([0-9A-Za-z_-]+)$");
      e = e || mrx(unescape(a.href), "(?:^|[^0-9A-Za-z_-])([0-9A-Za-z_-]+)$");
      if (e) {
        a.setAttribute("data-decrypted", "true");
        return a.href = decode(e, obfuscator.key);
      }
    }
  };

})(
  "undefined" !== typeof module && module.exports ||
  ((window.klg || (window.klg = {})).obfuscator = {})
);
