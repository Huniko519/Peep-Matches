(function( $, factory ){'use strict';
    $(function(){factory.call(this, $)}.bind(this));
}).call(window, window.jQuery, function( $ ){'use strict';

    var _ = {}, delay = 300;

    var ErrorFactory = {
        getSimpleError: function( msg )
        {
            return new Error(msg);
        },
        getTypeError: function( index, need, object )
        {
            return new TypeError(
                'Invalid Argument Exception. Expects parameter {$index} to be {$need}, {$object} given'
                    .replace('{$index}', index)
                    .replace('{$need}', need)
                    .replace('{$object}', getObjectType(object))
            );
        }
    };

    ['Object', 'Array'].forEach(function( item )
    {
        _['is' + item] = function( object )
        {
            return getObjectType(object) === item;
        };
    });

    _.isEmptyObject = function( object )
    {
        if ( !_.isObject(object) )
        {
            throw ErrorFactory.getTypeError(1, 'Object', object);
        }

        return Object.keys(object).length === 0;
    };

    function Themevisitor( index, dom )
    {
        this.index = index;
        this.element = dom;
        this.userData = {};
    }

    Themevisitor.prototype.required = ['src', 'url', 'displayName'];
    Themevisitor.prototype.run = fluent(function()
    {
        var userData = this.userData,
            avatar = $(this.element.querySelector('.ul_themevisitor_avatar')),
            avatarLink = $('a', avatar),

            infoBlock = $(this.element.querySelector('.ul_themevisitor_info')),
            userLink = $(infoBlock[0].querySelector('.ul_themevisitor_info_name a')),
            sexAge = $(this.element.querySelector('.info_sex_age')),
            address = $(this.element.querySelector('.info_address'));

        var avatarDelay = this.index <= 3 ? 0 : delay / 2;
        var infoDelay = this.index <= 3 ? delay / 2 : 0;

        setTimeout(function()
        {
            avatar.animate({opacity: 0}, {duration: 400, complete: function()
            {
                avatar.css('background-image', 'url(' + userData.src + ')');
                avatarLink.attr('href', userData.url);
                avatar.animate({opacity: 1}, {duration: 800});
            }});
        }, avatarDelay);

        setTimeout(function()
        {
            infoBlock.animate({opacity: 0}, {duration: 400, complete: function()
            {
                userLink.attr('href', userData.url).text(userData.displayName);
                address.text(userData.data.address || '');

                if ( userData.data.sex )
                {
                    sexAge.text(userData.data.sex + (userData.data.age ? ', ' + userData.data.age : ''));
                }
                else
                {
                    sexAge.text(userData.data.age || '');
                }

                infoBlock.animate({opacity: 1}, 800);
            }});
        }, infoDelay);
    });
    Themevisitor.prototype.assignUser = fluent(function( userData )
    {
        if ( !isIncludeRequiredProperty(userData, this.required) )
        {
            return;
        }

        this.userData = userData;

        var img = new Image();

        img.onload = img.onerror = function()
        {
            this.run();
        }.bind(this);
        img.src = this.userData.src;
    });

    if ( _.isEmptyObject(this.fadeUserParams) || !isIncludeRequiredProperty(this.fadeUserParams, ['userList']) )
    {
        throw ErrorFactory.getSimpleError('User list required');
    }

    var params = $.extend({}, this.fadeUserParams);
    var themevisitorItems = document.getElementById('fade-user-list').querySelectorAll('.ul_themevisitor_item');
    var themevisitorCollect = {};

    for ( var i = 0; i < params.max; i++ )
    {
        themevisitorCollect[i] = new Themevisitor(i + 1, themevisitorItems[i]);
    }

    preloadImage(getUserSrcList(params.userList.slice(params.max, params.max * 2)));

    setInterval(function()
    {
        var idList = params.userList;
        var indexList = idList.splice(params.max);
        params.userList = indexList.concat(idList);
        var _delay = 0;

        $.each(themevisitorCollect, function()
        {
            setTimeout(function()
            {
                this.assignUser(params.userList[this.index]);
            }.bind(this), _delay);

            _delay += delay;
        });

        preloadImage(getUserSrcList(params.userList.slice(params.max, params.max * 2)));
    }, 8000);

    function getObjectType( object )
    {
        return Object.prototype.toString.call(object).slice(8, -1);
    }

    function fluent( f )
    {
        return function()
        {
            f.apply(this, arguments);

            return this;
        }
    }

    function isIncludeRequiredProperty( object, required )
    {
        if ( !_.isObject(object) )
        {
            throw ErrorFactory.getTypeError(1, 'Object', object);
        }

        if ( !_.isArray(required) )
        {
            throw ErrorFactory.getTypeError(1, 'Array', required);
        }

        return required.every(function( item )
        {
            return object.hasOwnProperty(item);
        });
    }

    function getUserSrcList( userList )
    {
        var result = [];

        userList.forEach(function( user )
        {
            result.push(user.src);
        });

        return result;
    }

    function preloadImage( imgList )
    {
        imgList.forEach(function( src )
        {
            setTimeout(function( src )
            {
                new Image().src = src;
            }, 10, src);
        });
    }
});
