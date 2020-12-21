GoogleMapLocationHint = (function() {    
    var isShown = null;
    var CORNER_OFFSET = 7;

    var _clearTimeout = function( timeOut ) {
        if ( timeOut ) window.clearTimeout(timeOut);
    };

    var hintActions = {
        start:function() {
            this.show();
        },
        stop:function() {
            this.hide();
        },
        enter:function() {
            _clearTimeout(this.timeouts.stop);
        },
        leave:function() {
            this.stop();
        }
    };

    var _setTimeout = function( fnc, time ) {
        return window.setTimeout(fnc, time);
    };

    var _bind= function( fnc, obj ) {
        fnc = fnc || function(){};
        obj = obj || window;

        return function() {
            return fnc.apply(obj, arguments);
        };
    };

    var MapHint = function( delegate, target ) {
        
        this.hint = $($('#map-hint-template').html()).hide();
        this.body = this.hint.find('.map-hint-body');
        this.topCornerBody = this.hint.find('.map-hint-top-corner-wrap .map-hint-corner');
        this.bottomCornerBody = this.hint.find('.map-hint-bottom-corner-wrap .map-hint-corner');
        this.rightCornerBody = this.hint.find('.map-hint-right-corner-wrap .map-hint-corner');
        
        this.visible = false;
        this._targetChanged = false;

        $('body').append(this.hint);

        this.orientationClass = 'map-hint-top-left';

        this.timeouts = {
            start: null,
            stop: null,
            enter: null,
            leave: null
        };

        this.delegate = {};
        
        if ( delegate ) this.setDelegate(delegate);
        if ( target ) this.setTarget(target);

        this.hint.on('mouseenter.hint', _bind(hintActions.enter, this));
        this.hint.on('mouseleave.hint', _bind(hintActions.leave, this));

        _bind(this.delegate.construct, this)(); // Delegate method call
    };

    MapHint.prototype.START_TIMEOUT = 700;
    MapHint.prototype.STOP_TIMEOUT = 200;
    MapHint.prototype.SWITCH_TIMEOUT = 400;

    MapHint.prototype.setTarget = function( target ) {
        var oldTarget = this.target;

        this.target = $(target);
        this._targetChanged = true;

        this.target.data("hint", this);

        _bind(this.delegate.targetChange, this)(oldTarget); // Delegate method call
        
        if ( this.target.data("map-hint-zindex") ) {
            this.hint.css("z-index", this.target.data("map-hint-zindex"));
        }

        return this;
    };

    MapHint.prototype.setDelegate = function( delegate ) {
        this.delegate = delegate;

        return this;
    };
    
    MapHint.prototype.getDelegate = function() {
        return this.delegate;
    };

    MapHint.prototype.getSize = function() {
        return {
            width: this.hint.width(),
            height: this.hint.height()
        };
    };

    MapHint.prototype.getPosition = function( target ) {
        var offset, $window;

        target = target || this.target;
        offset = target.offset();
        $window = $(window);

        if ( !offset )
            return null;

        return {
            top: offset.top - $window.scrollTop(),
            left: offset.left - $window.scrollLeft(),
            right: $window.width() - offset.left - $window.scrollLeft(),
            bottom: $window.height() + $window.scrollTop() - offset.top
        };
    };

    MapHint.prototype.getOrientation = function( target ) {
        var position, size, orientation;

        target = target || this.target;

        size = this.getSize();
        position = this.getPosition(target);
        if ( !position ) 
            return null;

        orientation = {};
        orientation.top = size.height < position.top - CORNER_OFFSET;
        orientation.bottom = !orientation.top;
        orientation.right = size.width < position.right;
        orientation.left = !orientation.right;

        return orientation;
    };
    
    MapHint.prototype.refreshOrientation = function() {
        var offset, position, targetHeight, targetWidth, cornerOffset, cornerPosition, currentCorner,
            topCorner, bottomCorner, orientation, size, target, innerNodes;

        if ( _bind(this.delegate.beforeRefreshOrientation, this)() === false ) // Delegate method call
            return this;
            
        if ( !this.target ) return this;

        target = this.target;

        this.target.addClass('map-hint-target');

        if ( this.target.css("display") === "inline" ) {
            innerNodes = this.target.children().filter(function() {
                return $(this).is('img') || $(this).css("display") !== "inline";
            });

            if ( innerNodes.length > 0 ) {
                this.target.addClass('map-hint-target-block');

                target = innerNodes.first();
            }
        }

        topCorner = this.hint.find('.map-hint-top-corner-wrap');
        bottomCorner = this.hint.find('.map-hint-bottom-corner-wrap');

        this.hint.removeClass(this.orientationClass);

        if ( !this.delegate.refreshOrientation ) // Generic behaviour
        {
            targetHeight = target.outerHeight();
            targetWidth = target.outerWidth();

            offset = _bind(this.delegate.getOffset, this)(target); // Delegate method call
            offset = offset || target.offset();

            position = {
                top: offset.top + targetHeight,
                left: offset.left
            };

            size = this.getSize();
            orientation = this.getOrientation(target);
            if (!orientation)
                return this;

            cornerOffset = targetWidth / 2 - 5;
            cornerOffset = cornerOffset < 2 ? 2 : cornerOffset;
            cornerOffset = cornerOffset > size.width / 2 ? size.width / 2 : cornerOffset;

            if ( orientation.top && orientation.left ) {
                this.orientationClass = 'map-hint-top-left';
                position.top = offset.top - size.height - CORNER_OFFSET;
                position.left = offset.left - size.width + targetWidth;
                bottomCorner.css('right', cornerOffset);
                currentCorner = bottomCorner;
            } else if ( orientation.top && orientation.right ) {
                this.orientationClass = 'map-hint-top-right';
                position.top = offset.top - size.height - CORNER_OFFSET;
                bottomCorner.css('left', cornerOffset);
                currentCorner = bottomCorner;
            } else if ( orientation.bottom && orientation.left ) {
                this.orientationClass = 'map-hint-bottom-left';
                position.left = offset.left - size.width + targetWidth;
                topCorner.css('right', cornerOffset);
                currentCorner = topCorner;
            } else if ( orientation.bottom && orientation.right ) {
                this.orientationClass = 'map-hint-bottom-right';
                topCorner.css('left', cornerOffset);
                currentCorner = topCorner;
            }
            
            this.hint.css(position);
        }
        else
        {
            currentCorner = _bind(this.delegate.refreshOrientation, this)(target);
        }

        this.hint.addClass(this.orientationClass);
        this.hint.removeClass('map-hint-invisible');
        
        if ( currentCorner ) {
            cornerPosition = currentCorner.position();
        }

        this._targetChanged = false;

        _bind(this.delegate.afterRefreshOrientation, this)(orientation, position, cornerPosition, {
            width: targetWidth, height: targetHeight
        }); // Delegate method call

        return this;
    };
    
    
    
    MapHint.prototype.show = function() {
        if ( this.visible ) {
            
            if ( this._targetChanged ) {
                this.refreshOrientation();
            }

            return this;
        }

        if ( _bind(this.delegate.beforeShow, this)() === false ) // Delegate method call
            return this;
        
        if ( !this.target ) return this;

        this.hint.show();
        this.refreshOrientation();

        _bind(this.delegate.afterShow, this)(); // Delegate method call
        isShown = this;
        this.visible = true;

        return this;
    };

    MapHint.prototype.hide = function() {
        if ( !this.visible ) return this;

        if ( _bind(this.delegate.beforeHide, this)() === false ) // Delegate method call
            return this;
        
        _clearTimeout(this.timeouts.stop);

        this.hint.hide();
        isShown = null;
        this.visible = false;

        _bind(this.delegate.afterHide, this)(); // Delegate method call

        return this;
    };

    MapHint.prototype.start = function() {
        _bind(this.delegate.beforeStart, this)(); // Delegate method call

        if ( !this.target ) return this;

        _clearTimeout(this.timeouts.stop);
        _clearTimeout(this.timeouts.start);

        this.timeouts.start = _setTimeout(_bind(hintActions.start, this), !!isShown ? this.SWITCH_TIMEOUT : this.START_TIMEOUT);

        _bind(this.delegate.afterStart, this)(); // Delegate method call

        return this;
    };

    MapHint.prototype.stop = function() {
        _bind(this.delegate.beforeStop, this)(); // Delegate method call

        _clearTimeout(this.timeouts.start);
        _clearTimeout(this.timeouts.stop);
        this.timeouts.stop = _setTimeout(_bind(hintActions.stop, this), this.STOP_TIMEOUT);

        _bind(this.delegate.afterStop, this)(); // Delegate method call

        return this;
    };

    MapHint.prototype.setContent = function( content ) {
        this.body.empty().append(content);

        _bind(this.delegate.contentChange, this)(); // Delegate method

        this.hint.addClass('map-hint-invisible');
        _setTimeout(_bind(this.refreshOrientation, this), 0);

        return this;
    };

    MapHint.prototype.setTopCorner = function( content ) {
        this.topCornerBody.empty().append(content);

        return this;
    };

    MapHint.prototype.setBottomCorner = function( content ) {
        this.bottomCornerBody.empty().append(content);

        return this;
    };
    
    MapHint.prototype.setRightCorner = function( content ) {
        this.rightCornerBody.empty().append(content);

        return this;
    };

    return {
        createHint: function( delegate, node ) {
            if ( !node ) {
                return new MapHint(delegate);
            }

            var hint = this.getHint(node);

            if ( hint ) {
                hint.hide();
            }

            return new MapHint(delegate, $(node));
        },

        getHint: function( node ) {
            var target = $(node);

            if ( target.data("hint") ) {
                target.data("hint").setTarget(target);

                return target.data("hint");
            }

            return null;
        },

        getHintOrCreate: function( delegate, node ) {
            var hint = this.getHint(node);
            if ( hint ) {
                if ( delegate ) {
                    hint.setDelegate(delegate);
                }

                return hint;
            }

            return this.createHint(delegate, node);
        },

        isAnyShown: function() {
            return !!isShown;
        },

        getShown: function() {
            return isShown;
        },

        init: function() {

        }
    };
})();


GoogleMapLocationHint.LAUNCHER = function() { 
    
    var _cache = {};
    
    var displayMap = function(elementId,location) {

        var options =  {
            zoom: 2,
            minZoom: 2,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            scrollwheel: false,
        };

        if ( !window.map )
        {
            window.map = {};
        }
        
        var sw = new google.maps.LatLng(location.southWestLat,location.southWestLng);
        var ne = new google.maps.LatLng(location.northEastLat,location.northEastLng);

        var bounds = new google.maps.LatLngBounds(sw, ne );
        
        if ( !window.map[elementId] )
        {
            window.map[elementId] = new window.PEEP_GoogleMap(elementId);
            window.map[elementId].initialize(options);
            
            window.map[elementId].fitBounds(bounds); 
            window.map[elementId].addPoint(location.lat,location.lng, null, null, null, _marker_icon_path);

            window.map[elementId].createMarkerCluster();
        }
        
        return bounds;
    }
    
    var Delegate = function(location) {
        this.init(location);
//        this.topCorener(location);
//        this.bottomCorener(location);
    };

    Delegate.prototype.init = function(location) {
        this.resize = true;
        this.hash = GoogleMapLocationHint.UTILS.HashCode.value(location);
        this.elementId = "#map_" + this.hash ;
        this.element = $(this.elementId );
        
        if ( $(this.elementId).size() == 0 )
        {
            this.element = $("<div id='"+this.elementId+"' style='width:300px;height:300px;'></div>");
        }
        
        this.location = location;
    };
    
//    Delegate.prototype.topCorener = function(location) {
//        this.topCornerId = "#map_top_corner_" + this.hash ;
//        this.topCornerElement = $(this.topCornerId );
//        
//        if ( $(this.topCornerId).size() == 0 )
//        {
//            this.topCornerElement = $("<div id='"+this.topCornerId+"' style='width:300px;height:300px; display:none'></div>");
//            $("body").append(this.topCornerElement);
//        }
//        
//        this.location = location;
//    };
//    
//    Delegate.prototype.bottomCorener = function(location) {
//        this.bottomCornerId = "#map_bottom_corner_" + this.hash ;
//        this.bottomCornerElement = $(this.bottomCornerId );
//        
//        if ( $(this.bottomCornerId).size() == 0 )
//        {
//            this.bottomCornerElement = $("<div id='"+this.bottomCornerId+"' style='width:300px;height:320px; display:none'></div>");
//            $("body").append(this.bottomCornerElement);
//        }
//        
//        this.location = location;
//    };

    Delegate.prototype.construct = function() {
//        this.setTopCorner(this.delegate.topCornerElement);
//        this.setBottomCorner(this.delegate.bottomCornerElement);
        this.setContent(this.delegate.element);
        displayMap(this.delegate.elementId, this.delegate.location);
//        displayMap(this.delegate.topCornerId , this.delegate.location);
//        displayMap(this.delegate.bottomCornerId , this.delegate.location);
        
    };

    Delegate.prototype.afterRefreshOrientation = function( orientation, position, cornerPosition ) {
        if ( cornerPosition ) {
            this.topCornerBody.find(".uhint-corner-cover").css("margin-left", -(cornerPosition.left + 0.5));
        }
        
        window.map[this.delegate.elementId].resize(); 
        window.map[this.delegate.elementId].resetLastBounds();
        
//        window.map[this.delegate.topCornerId].resize(); 
//        window.map[this.delegate.topCornerId].resetLastBounds();
//        
//        window.map[this.delegate.bottomCornerId].resize(); 
//        window.map[this.delegate.bottomCornerId].resetLastBounds();
    };

    Delegate.prototype.beforeShow = function() {
        var self = this;

//        this.delegate.bottomCornerElement.show();
//        this.delegate.topCornerElement.show();
        if ( GoogleMapLocationHint.isAnyShown() && GoogleMapLocationHint.getShown() !== this ) GoogleMapLocationHint.getShown().hide();

        if ( PEEP.getActiveFloatBox() ) {
            this.hint.removeClass("map-hint-from-floatbox").addClass("map-hint-from-floatbox");

            PEEP.getActiveFloatBox().bind("close", function() {
                self.hint.removeClass("map-hint-from-floatbox");
                self.hide();
            });
        }        
    };

    Delegate.prototype.beforeStart = function() {
        if ( GoogleMapLocationHint.isAnyShown() && GoogleMapLocationHint.getShown().timeouts.stop ) {
            window.clearTimeout(GoogleMapLocationHint.getShown().timeouts.stop);
        }
    };

    Delegate.prototype.beforeStop = function() {
        
        if ( GoogleMapLocationHint.isAnyShown() && GoogleMapLocationHint.getShown() !== this ) {
            GoogleMapLocationHint.getShown().stop();
            
            this.delegate.element.hide();
//            this.delegate.bottomCornerElement.hide();
//            this.delegate.topCornerElement.hide();
            
            $("body").append(this.delegate.element);
//            $("body").append(this.delegate.bottomCornerElement);
//            $("body").append(this.delegate.topCornerElement);
        }
    };

    return {
        getHint: function( target, icon_path ) {
            var delegateConstructor;
            
            _marker_icon_path = icon_path;
            
            var hint = GoogleMapLocationHint.getHint(target);

            var hash = GoogleMapLocationHint.UTILS.HashCode.value(target.location);

            if ( !hint && _cache[hash] ) {
                hint = _cache[hash];
                hint.setTarget(target);
            }
            
            if ( hint )
            {
                delegateConstructor = Delegate;
                
                hint.setDelegate(new delegateConstructor(target.location));
            }

            return hint;
        },

        createHint: function( target ) {
            var delegateConstructor;
            
            delegateConstructor = Delegate;
            
            var hint = GoogleMapLocationHint.createHint(new delegateConstructor(target.location), target);
            var hash = GoogleMapLocationHint.UTILS.HashCode.value(target.location);
            _cache[hash] = hint;

            return hint;
        },

        init: function(icon_path) {
            GoogleMapLocationHint.init();

            var queryTimeOut;

            $(document).on('mouseenter.hint', '.map-hint-target', function( event ) {
                var self = this;
                
                if ( !$(this).data('location') ) return;
                
                self.location = $(this).data('location');
                
                var launcher = GoogleMapLocationHint.LAUNCHER();
                var hint = launcher.getHint(this, icon_path);

                if ( hint ) {
                    hint.start();
                } else {
                    queryTimeOut = window.setTimeout(function() {
                        launcher.createHint(self).start();
                    }, 200);//GoogleMapLocationHint.isAnyShown() ? 50 : 150);
                }

                // Prevents an appearing of the standard tooltip
                var target = $(event.target);
                if ( target.data().tod ) {
                    window.clearTimeout(target.data().tod);
                }
            });

            $(document).on('mouseleave.hint', '.map-hint-target', function() {
                if ( !$(this).data('location')  ) return;

                window.clearTimeout(queryTimeOut);

                var hint = GoogleMapLocationHint.getHint(this);
                if ( hint ) hint.stop();
            });

            $(document).on('click.hint', '.map-hint-target', function() {
                if ( !$(this).data('location')  ) return;

                var hint = GoogleMapLocationHint.getHint(this);
                if ( hint ) hint.stop();
            });
        }
    };
}

GoogleMapLocationHint.UTILS = {
    MD5: function (string) {

        function RotateLeft(lValue, iShiftBits) {
            return (lValue << iShiftBits) | (lValue >>> (32 - iShiftBits));
        }

        function AddUnsigned(lX, lY) {
            var lX4, lY4, lX8, lY8, lResult;
            lX8 = (lX & 0x80000000);
            lY8 = (lY & 0x80000000);
            lX4 = (lX & 0x40000000);
            lY4 = (lY & 0x40000000);
            lResult = (lX & 0x3FFFFFFF) + (lY & 0x3FFFFFFF);
            if (lX4 & lY4) {
                return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
            }
            if (lX4 | lY4) {
                if (lResult & 0x40000000) {
                    return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
                } else {
                    return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
                }
            } else {
                return (lResult ^ lX8 ^ lY8);
            }
        }

        function F(x, y, z) {
            return (x & y) | ((~x) & z);
        }
        function G(x, y, z) {
            return (x & z) | (y & (~z));
        }
        function H(x, y, z) {
            return (x ^ y ^ z);
        }
        function I(x, y, z) {
            return (y ^ (x | (~z)));
        }

        function FF(a, b, c, d, x, s, ac) {
            a = AddUnsigned(a, AddUnsigned(AddUnsigned(F(b, c, d), x), ac));
            return AddUnsigned(RotateLeft(a, s), b);
        }
        ;

        function GG(a, b, c, d, x, s, ac) {
            a = AddUnsigned(a, AddUnsigned(AddUnsigned(G(b, c, d), x), ac));
            return AddUnsigned(RotateLeft(a, s), b);
        }
        ;

        function HH(a, b, c, d, x, s, ac) {
            a = AddUnsigned(a, AddUnsigned(AddUnsigned(H(b, c, d), x), ac));
            return AddUnsigned(RotateLeft(a, s), b);
        }
        ;

        function II(a, b, c, d, x, s, ac) {
            a = AddUnsigned(a, AddUnsigned(AddUnsigned(I(b, c, d), x), ac));
            return AddUnsigned(RotateLeft(a, s), b);
        }
        ;

        function ConvertToWordArray(string) {
            var lWordCount;
            var lMessageLength = string.length;
            var lNumberOfWords_temp1 = lMessageLength + 8;
            var lNumberOfWords_temp2 = (lNumberOfWords_temp1 - (lNumberOfWords_temp1 % 64)) / 64;
            var lNumberOfWords = (lNumberOfWords_temp2 + 1) * 16;
            var lWordArray = Array(lNumberOfWords - 1);
            var lBytePosition = 0;
            var lByteCount = 0;
            while (lByteCount < lMessageLength) {
                lWordCount = (lByteCount - (lByteCount % 4)) / 4;
                lBytePosition = (lByteCount % 4) * 8;
                lWordArray[lWordCount] = (lWordArray[lWordCount] | (string.charCodeAt(lByteCount) << lBytePosition));
                lByteCount++;
            }
            lWordCount = (lByteCount - (lByteCount % 4)) / 4;
            lBytePosition = (lByteCount % 4) * 8;
            lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80 << lBytePosition);
            lWordArray[lNumberOfWords - 2] = lMessageLength << 3;
            lWordArray[lNumberOfWords - 1] = lMessageLength >>> 29;
            return lWordArray;
        }
        ;

        function WordToHex(lValue) {
            var WordToHexValue = "", WordToHexValue_temp = "", lByte, lCount;
            for (lCount = 0; lCount <= 3; lCount++) {
                lByte = (lValue >>> (lCount * 8)) & 255;
                WordToHexValue_temp = "0" + lByte.toString(16);
                WordToHexValue = WordToHexValue + WordToHexValue_temp.substr(WordToHexValue_temp.length - 2, 2);
            }
            return WordToHexValue;
        }
        ;

        function Utf8Encode(string) {
            string = string.replace(/\r\n/g, "\n");
            var utftext = "";

            for (var n = 0; n < string.length; n++) {

                var c = string.charCodeAt(n);

                if (c < 128) {
                    utftext += String.fromCharCode(c);
                }
                else if ((c > 127) && (c < 2048)) {
                    utftext += String.fromCharCode((c >> 6) | 192);
                    utftext += String.fromCharCode((c & 63) | 128);
                }
                else {
                    utftext += String.fromCharCode((c >> 12) | 224);
                    utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                    utftext += String.fromCharCode((c & 63) | 128);
                }

            }

            return utftext;
        }
        ;

        var x = Array();
        var k, AA, BB, CC, DD, a, b, c, d;
        var S11 = 7, S12 = 12, S13 = 17, S14 = 22;
        var S21 = 5, S22 = 9, S23 = 14, S24 = 20;
        var S31 = 4, S32 = 11, S33 = 16, S34 = 23;
        var S41 = 6, S42 = 10, S43 = 15, S44 = 21;

        string = Utf8Encode(string);

        x = ConvertToWordArray(string);

        a = 0x67452301;
        b = 0xEFCDAB89;
        c = 0x98BADCFE;
        d = 0x10325476;

        for (k = 0; k < x.length; k += 16) {
            AA = a;
            BB = b;
            CC = c;
            DD = d;
            a = FF(a, b, c, d, x[k + 0], S11, 0xD76AA478);
            d = FF(d, a, b, c, x[k + 1], S12, 0xE8C7B756);
            c = FF(c, d, a, b, x[k + 2], S13, 0x242070DB);
            b = FF(b, c, d, a, x[k + 3], S14, 0xC1BDCEEE);
            a = FF(a, b, c, d, x[k + 4], S11, 0xF57C0FAF);
            d = FF(d, a, b, c, x[k + 5], S12, 0x4787C62A);
            c = FF(c, d, a, b, x[k + 6], S13, 0xA8304613);
            b = FF(b, c, d, a, x[k + 7], S14, 0xFD469501);
            a = FF(a, b, c, d, x[k + 8], S11, 0x698098D8);
            d = FF(d, a, b, c, x[k + 9], S12, 0x8B44F7AF);
            c = FF(c, d, a, b, x[k + 10], S13, 0xFFFF5BB1);
            b = FF(b, c, d, a, x[k + 11], S14, 0x895CD7BE);
            a = FF(a, b, c, d, x[k + 12], S11, 0x6B901122);
            d = FF(d, a, b, c, x[k + 13], S12, 0xFD987193);
            c = FF(c, d, a, b, x[k + 14], S13, 0xA679438E);
            b = FF(b, c, d, a, x[k + 15], S14, 0x49B40821);
            a = GG(a, b, c, d, x[k + 1], S21, 0xF61E2562);
            d = GG(d, a, b, c, x[k + 6], S22, 0xC040B340);
            c = GG(c, d, a, b, x[k + 11], S23, 0x265E5A51);
            b = GG(b, c, d, a, x[k + 0], S24, 0xE9B6C7AA);
            a = GG(a, b, c, d, x[k + 5], S21, 0xD62F105D);
            d = GG(d, a, b, c, x[k + 10], S22, 0x2441453);
            c = GG(c, d, a, b, x[k + 15], S23, 0xD8A1E681);
            b = GG(b, c, d, a, x[k + 4], S24, 0xE7D3FBC8);
            a = GG(a, b, c, d, x[k + 9], S21, 0x21E1CDE6);
            d = GG(d, a, b, c, x[k + 14], S22, 0xC33707D6);
            c = GG(c, d, a, b, x[k + 3], S23, 0xF4D50D87);
            b = GG(b, c, d, a, x[k + 8], S24, 0x455A14ED);
            a = GG(a, b, c, d, x[k + 13], S21, 0xA9E3E905);
            d = GG(d, a, b, c, x[k + 2], S22, 0xFCEFA3F8);
            c = GG(c, d, a, b, x[k + 7], S23, 0x676F02D9);
            b = GG(b, c, d, a, x[k + 12], S24, 0x8D2A4C8A);
            a = HH(a, b, c, d, x[k + 5], S31, 0xFFFA3942);
            d = HH(d, a, b, c, x[k + 8], S32, 0x8771F681);
            c = HH(c, d, a, b, x[k + 11], S33, 0x6D9D6122);
            b = HH(b, c, d, a, x[k + 14], S34, 0xFDE5380C);
            a = HH(a, b, c, d, x[k + 1], S31, 0xA4BEEA44);
            d = HH(d, a, b, c, x[k + 4], S32, 0x4BDECFA9);
            c = HH(c, d, a, b, x[k + 7], S33, 0xF6BB4B60);
            b = HH(b, c, d, a, x[k + 10], S34, 0xBEBFBC70);
            a = HH(a, b, c, d, x[k + 13], S31, 0x289B7EC6);
            d = HH(d, a, b, c, x[k + 0], S32, 0xEAA127FA);
            c = HH(c, d, a, b, x[k + 3], S33, 0xD4EF3085);
            b = HH(b, c, d, a, x[k + 6], S34, 0x4881D05);
            a = HH(a, b, c, d, x[k + 9], S31, 0xD9D4D039);
            d = HH(d, a, b, c, x[k + 12], S32, 0xE6DB99E5);
            c = HH(c, d, a, b, x[k + 15], S33, 0x1FA27CF8);
            b = HH(b, c, d, a, x[k + 2], S34, 0xC4AC5665);
            a = II(a, b, c, d, x[k + 0], S41, 0xF4292244);
            d = II(d, a, b, c, x[k + 7], S42, 0x432AFF97);
            c = II(c, d, a, b, x[k + 14], S43, 0xAB9423A7);
            b = II(b, c, d, a, x[k + 5], S44, 0xFC93A039);
            a = II(a, b, c, d, x[k + 12], S41, 0x655B59C3);
            d = II(d, a, b, c, x[k + 3], S42, 0x8F0CCC92);
            c = II(c, d, a, b, x[k + 10], S43, 0xFFEFF47D);
            b = II(b, c, d, a, x[k + 1], S44, 0x85845DD1);
            a = II(a, b, c, d, x[k + 8], S41, 0x6FA87E4F);
            d = II(d, a, b, c, x[k + 15], S42, 0xFE2CE6E0);
            c = II(c, d, a, b, x[k + 6], S43, 0xA3014314);
            b = II(b, c, d, a, x[k + 13], S44, 0x4E0811A1);
            a = II(a, b, c, d, x[k + 4], S41, 0xF7537E82);
            d = II(d, a, b, c, x[k + 11], S42, 0xBD3AF235);
            c = II(c, d, a, b, x[k + 2], S43, 0x2AD7D2BB);
            b = II(b, c, d, a, x[k + 9], S44, 0xEB86D391);
            a = AddUnsigned(a, AA);
            b = AddUnsigned(b, BB);
            c = AddUnsigned(c, CC);
            d = AddUnsigned(d, DD);
        }

        var temp = WordToHex(a) + WordToHex(b) + WordToHex(c) + WordToHex(d);

        return temp.toLowerCase();
    },
    HashCode: function () {
        var serialize = function (object) {
            // Private
            var type, serializedCode = "";

            type = typeof object;

            if (type === 'object') {
                var element;

                for (element in object) {
                    serializedCode += "[" + type + ":" + element + serialize(object[element]) + "]";
                }

            } else if (type === 'function') {
                serializedCode += "[" + type + ":" + object.toString() + "]";
            } else {
                serializedCode += "[" + type + ":" + object + "]";
            }

            return serializedCode.replace(/\s/g, "");
        }

        // Public, API
        return {
            value: function (object) {
                return GoogleMapLocationHint.UTILS.MD5(serialize(object));
            }
        };
    }()

};


