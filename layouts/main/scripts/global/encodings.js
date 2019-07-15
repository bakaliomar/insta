var Encodings = function () 
{
    return {
        convertFromDecToHex: function(text)
        {
            var hexequiv = new Array ("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F");
            return hexequiv[(text >> 4) & 0xF] + hexequiv[text & 0xF]; 
        },
        encodeToUnicode: function (text) 
        {
            var highsurrogate = 0;
            var suppCP;
            var outputString = '';
            for (var i = 0; i < text.length; i++) 
            {
                    var cc = text.charCodeAt(i); 
                    
                    if (cc < 0 || cc > 0xFFFF) 
                    {
                        alert('Unexpected charCodeAt result, cc=' + cc + '!');
                    }
                    
                    if (highsurrogate != 0) 
                    {  
                        if (0xDC00 <= cc && cc <= 0xDFFF) 
                        {
                            suppCP = 0x10000 + ((highsurrogate - 0xD800) << 10) + (cc - 0xDC00); 
                            outputString += ' ' + Encodings.convertFromDecToHex(0xF0 | ((suppCP>>18) & 0x07)) + ' ' + Encodings.convertFromDecToHex(0x80 | ((suppCP>>12) & 0x3F)) + ' ' + Encodings.convertFromDecToHex(0x80 | ((suppCP>>6) & 0x3F)) + ' ' + Encodings.convertFromDecToHex(0x80 | (suppCP & 0x3F));
                            highsurrogate = 0;
                            continue;
                        }
                        else 
                        {
                            outputString += 'Error in convertCharStr2UTF8: low surrogate expected, cc=' + cc + '!';
                            highsurrogate = 0;
                        }
                    }
                    if (0xD800 <= cc && cc <= 0xDBFF) 
                    { 
                        highsurrogate = cc;
                    }
                    else 
                    {
                        if (cc <= 0x7F) { outputString += ' ' + Encodings.convertFromDecToHex(cc); }
                        else if (cc <= 0x7FF) { outputString += ' ' + Encodings.convertFromDecToHex(0xC0 | ((cc>>6) & 0x1F)) + ' ' + Encodings.convertFromDecToHex(0x80 | (cc & 0x3F)); } 
                        else if (cc <= 0xFFFF) { outputString += ' ' + Encodings.convertFromDecToHex(0xE0 | ((cc>>12) & 0x0F)) + ' ' + Encodings.convertFromDecToHex(0x80 | ((cc>>6) & 0x3F)) + ' ' + Encodings.convertFromDecToHex(0x80 | (cc & 0x3F)); } 
                    }
            }
            
            return outputString.substring(1);
        }
    };
}();
