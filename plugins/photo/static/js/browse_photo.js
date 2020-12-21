(function( $, params, logic )
{'use strict';
    var actionStr;

    switch ( params.listType )
    {
        case 'albums':
            actionStr = 'setURL,setDescription';
            break;
        case 'albumPhotos':
            actionStr = 'setRate,setCommentCount,setDescription,bindEvents';
            break;
        case 'userPhotos':
            actionStr = 'setAlbumInfo,setRate,setCommentCount,setDescription,bindEvents';
            break;
        default:
            actionStr = 'setAlbumInfo,setUserInfo,setRate,setCommentCount,setDescription,bindEvents';
            break;
    }

    this.browsePhoto = logic.call(this, $, $.extend({actions: actionStr.split(',')}, params));
}.call(window, window.jQuery, window.browsePhotoParams, function( $, params, undf )
{'use strict';
    var root = this, utils = photoUtils;

    var BaseObject = (function()
    {
        function BaseObject()
        {
            this.self = utils.bind(this);
        }

        return BaseObject;
    })();

    var Slot = (function( BaseObject )
    {
        utils.extend(Slot, BaseObject);

        function Slot( slot, listType )
        {
            Slot._super.call(this);

            this.node = $('#browse-photo-item-prototype').clone();
            this.node
                .attr('id', 'photo-item-' + slot.id)
                .data('slotId', slot.id);
            this.data = $.extend({listType: listType}, slot);
        }

        Slot.prototype.setInfo = utils.fluent(function( data )
        {
            $.extend(this.data, data);

            params.actions.forEach(function( action )
            {
                this[action]();
            }, this);
        });

        Slot.prototype.setURL = utils.fluent(function()
        {
            this.node.off().on('click', this.self(function()
            {
                window.location = this.data.albumUrl;
            }));
        });

        Slot.prototype.setDescription = utils.fluent(function()
        {
            var description, data = this.data;

            if ( data.listType != 'albums' )
            {
                description = utils.descToHashtag(utils.truncate(data.description), utils.getHashtags(data.description), params.tagUrl);
            }
            else
            {
                description = PEEP.getLanguageText('photo', 'album_description', {
                    desc: utils.truncate(data.name, 50),
                    count: data.count || 0
                });
            }

            var event = {text: description};
            PEEP.trigger('photo.onSetDescription', event);
            description = event.text.trim();

            if ( description )
            {
                this.node.find('.peep_photo_item_info_description').show().find('.peep_photo_description').html(description);
            }
            else
            {
                this.node.find('.peep_photo_item_info_description').hide();
            }
        });

        Slot.prototype.setRate = utils.fluent(function()
        {
            var data = this.data;

            if ( !data.rateInfo || data.userScore === undf )
            {
                return;
            }

            if ( data.userScore > 0 )
            {
                this.node.find('.rate_title').html(PEEP.getLanguageText('photo', 'rating_your', {
                    count: data.rateInfo.rates_count,
                    score: data.userScore
                }));
            }
            else
            {
                this.node.find('.rate_title').html(PEEP.getLanguageText('photo', 'rating_total', {
                    count: data.rateInfo.rates_count
                }));
            }

            this.node.find('.active_rate_list').css('width', (data.rateInfo.avg_score * 20) + '%');

            var self = this;

            var event = {
                canRate: true,
                photo: this.data
            };
            PEEP.trigger('photo.canRate', event, this.node);

            if ( !event.canRate )
            {
                $('.rate_item', this.node).off().on('click', function( e )
                {
                    e.stopPropagation();

                    var event = {
                        msg: ''
                    };
                    PEEP.trigger('photo.canRateMessage', event, self.node);
                    PEEP.error(event.msg);
                });
            }
            else
            {
                $('.rate_item', this.node).off().on('click', function( event )
                {
                    event.stopPropagation();

                    if ( params.rateUserId === 0 )
                    {
                        PEEP.error(PEEP.getLanguageText('base', 'rate_cmp_auth_error_message'));

                        return;
                    }
                    else if ( data.userId == params.rateUserId )
                    {
                        PEEP.error(PEEP.getLanguageText('base', 'rate_cmp_owner_cant_rate_error_message'));

                        return;
                    }

                    var rate = $(this).index() + 1;

                    $.ajax({
                        url: params.getPhotoURL,
                        dataType: 'json',
                        data: {
                            ajaxFunc: 'ajaxRate',
                            entityId: data.id,
                            rate: rate,
                            ownerId: data.userId
                        },
                        cache: false,
                        type: 'POST',
                        success: function( result, textStatus, jqXHR )
                        {
                            if ( result )
                            {
                                switch ( result.result )
                                {
                                    case true:
                                        PEEP.info(result.msg);
                                        self.node.find('.active_rate_list').css('width', (result.rateInfo.avg_score * 20) + '%');
                                        self.node.find('.rate_title').html(PEEP.getLanguageText('photo', 'rating_your', {
                                            count: result.rateInfo.rates_count,
                                            score: rate
                                        }));
                                        self.data.rateInfo.avg_score = result.rateInfo.avg_score;
                                        break;
                                    case false:
                                    default:
                                        PEEP.error(result.error);
                                        break;
                                }
                            }
                            else
                            {
                                PEEP.error('Server error');
                            }
                        },
                        error: function( jqXHR, textStatus, errorThrown )
                        {
                            throw textStatus;
                        }
                    });
                }).hover(function()
                {
                    $(this).prevAll().add(this).addClass('active');
                    self.node.find('.active_rate_list').css('width', '0px');
                }, function()
                {
                    $(this).prevAll().add(this).removeClass('active');
                    self.node.find('.active_rate_list').css('width', (data.rateInfo.avg_score * 20) + '%');
                });
            }
        });

        Slot.prototype.setCommentCount = utils.fluent(function()
        {
            this.node.find('.peep_photo_comment_count a').html('<b>' + this.data.commentCount + '</b>');
        });

        Slot.prototype.setAlbumInfo = utils.fluent(function()
        {
            var info = this.node.find('.peep_photo_item_info_album').show()
                .find('a').attr('href', this.data.albumUrl);

            if ( params.classicMode )
            {
                info.find('b').html(this.data.albumName);
            }
            else
            {
                info.html(this.data.albumName);
            }
        });

        Slot.prototype.setUserInfo = utils.fluent(function()
        {
            var user = this.node.find('.peep_photo_by_user').show()
                .find('a').attr('href', this.data.userUrl);

            if ( params.classicMode )
            {
                user.find('b').html(this.data.userName);
            }
            else
            {
                user.html(this.data.userName);
            }
        });

        Slot.prototype.bindEvents = utils.fluent(function()
        {
            var event = {
                selectors: [
                    '.peep_photo_item img'
                ]
            };
            PEEP.trigger('photo.collectShowPhoto', event);
            this.node.off().on('click', event.selectors.join(','), this.self(function()
            {
                var data = this.data,
                    img = this.node.find('img')[0],
                    dim = {mainUrl: data.url};

                if ( data.dimension && data.dimension.length )
                {
                    try
                    {
                        dim.main = JSON.parse(data.dimension).main;
                    }
                    catch( e )
                    {
                        dim.main = [img.naturalWidth, img.naturalHeight];
                    }
                }
                else
                {
                    dim.main = [img.naturalWidth, img.naturalHeight];
                }

                photoView.setId(data.id, data.listType, SlotManager().getMoreData(), dim, data);
            }));
        });

        return Slot;
    })(BaseObject);

    var SlotManager = (function( BaseObject )
    {
        utils.extend(SlotManager, BaseObject);

        var instance;

        function SlotManager()
        {
            if ( instance !== undf )
            {
                return instance;
            }

            if ( !(this instanceof SlotManager) )
            {
                return new SlotManager();
            }

            SlotManager._super.call(this);

            this.list = new List(this);
            this.request = null;
            this.listType = params.listType || 'latest';

            this.reset();

            instance = this;
        }

        SlotManager.prototype.load = utils.fluent(function( data )
        {
            data = data || {};

            if ( this.isCompleted ) return;

            if ( this.request && this.request.readyState !== 4 )
            {
                try
                {
                    this.request.abort();
                }
                catch ( ignore ) { }
            }

            this.request = $.ajax({
                url: params.getPhotoURL,
                dataType: 'json',
                data: $.extend({
                    ajaxFunc: params.action || 'getPhotoList',
                    listType: params.listType || 'latest',
                    offset: ++this.offset
                }, this.getMoreData(), data),
                cache: false,
                type: 'POST',
                beforeSend: this.self(function( jqXHR, settings )
                {
                    this.list.showLoader();
                    this.data = null;

                    if ( data.hasOwnProperty('listType') )
                    {
                        this.listType = data.listType;
                    }
                }),
                success: this.self(function( response, textStatus, jqXHR )
                {
                    if ( response && response.status )
                    {
                        utils.includeScriptAndStyle(response.scripts);

                        switch ( response.status )
                        {
                            case 'success':
                                this.buildSlotList(response.data);
                                break;
                            case 'error':
                            default:
                                PEEP.error(response.msg);
                                break;
                        }
                    }
                    else
                    {
                        PEEP.error('Server error');
                    }
                }),
                error: function( jqXHR, textStatus, errorThrown )
                {
                    throw textStatus;
                }
            });
        });

        SlotManager.prototype.getMoreData = function()
        {
            switch ( this.listType )
            {
                case 'albums':
                case 'userPhotos':
                    return $.extend({}, (this.modified ? {offset: 1, idList: Object.keys(this.cache)} : {}), {
                        userId: params.userId
                    });
                case 'albumPhotos':
                    return $.extend({}, (this.modified ? {offset: 1, idList: Object.keys(this.cache)} : {}), {
                        albumId: params.albumId
                    });
                default:
                    if ( ['user', 'hash', 'desc', 'all', 'tag'].indexOf(this.listType) !== -1 )
                    {
                        return SearchEngine().getSearchData();
                    }

                    return {};
            }
        };

        SlotManager.prototype.buildSlotList = utils.fluent(function( data )
        {
            if ( !data || utils.isEmptyArray(data.photoList) )
            {
                this.list.hideLoader();

                return;
            }
            else if ( data.photoList.length < 20 )
            {
                this.isCompleted = true;
            }

            this.data = data;
            this.uniqueList = data.unique;

            this.backgroundLoad(this.data.photoList).buildNewOne();
        });

        SlotManager.prototype.buildPhotoItem = utils.fluent(function( slot )
        {
            if ( slot === undf )
            {
                this.list.hideLoader();

                this.isTimeToLoad() ? this.unbindUI().load() : this.bindUI();

                return;
            }
            else if ( slot.unique !== this.uniqueList )
            {
                return;
            }

            var slotItem = new Slot(slot, this.listType);
            var slotData = this.getSlotData(slot);

            slotItem.setInfo(slotData);
            PEEP.trigger('photo.onRenderPhotoItem', [slot], slotItem.node);

            this.list.buildByMode(slotItem);
            this.cache[slotItem.data.id] = slotItem;
        });

        SlotManager.prototype.removePhotoItems = function( idList )
        {
            if ( utils.isEmptyArray(idList) )
            {
                return false;
            }

            var removed = idList.reduce(this.self(function( result, item )
            {
                if ( this.cache.hasOwnProperty(item) )
                {
                    var slot = this.cache[item];

                    result.push(slot.data.id);
                    slot.node.remove();
                    delete this.cache[item];
                }

                return result;
            }), []);

            if ( removed.length !== 0 )
            {
                this.modified = true;
                this.list.reorder();

                if ( this.isTimeToLoad() )
                {
                    this.load();
                }

                PEEP.trigger('photo.onRemovePhotoItems', {removed: removed});
            }

            return removed;
        };

        SlotManager.prototype.getSlotData = function( slot )
        {
            if ( !slot )
            {
                throw new TypeError('"Slot" required');
            }

            return params.actions.reduce(this.self(function( data, action )
            {
                switch ( action )
                {
                    case 'setUserInfo':
                        data.userUrl = this.data.userUrlList[slot.userId];
                        data.userName = this.data.displayNameList[slot.userId];
                        break;
                    case 'setAlbumInfo':
                        data.albumUrl = this.data.albumUrlList[slot.albumId];
                        data.albumName = this.data.albumNameList[slot.albumId].name;
                        break;
                    case 'setRate':
                        data.rateInfo = this.data.rateInfo[slot.id];
                        data.userScore = this.data.userScore[slot.id];
                        break;
                    case 'setCommentCount':
                        data.commentCount = this.data.commentCount[slot.id];
                        break;
                }

                return data;
            }), {});
        };

        SlotManager.prototype.buildNewOne = utils.fluent(function()
        {
            if ( this.data && this.data.photoList )
            {
                this.buildPhotoItem(this.data.photoList.shift());
            }
        });

        SlotManager.prototype.isTimeToLoad = function()
        {
            var win = $(root), max = Math.max(
                document.body.scrollHeight,
                document.body.offsetHeight,
                document.body.clientHeight,
                document.documentElement.scrollHeight,
                document.documentElement.offsetHeight,
                document.documentElement.clientHeight
            );

            return max - (win.scrollTop() + win.height()) <= 200;
        };

        SlotManager.prototype.unbindUI = utils.fluent(function()
        {
            $(root).off('scroll.browse_photo resize.browse_photo');
        });

        SlotManager.prototype.bindUI = utils.fluent(function()
        {
            $(root).on('scroll.browse_photo resize.browse_photo', this.self(function()
            {
                if ( this.isTimeToLoad() )
                {
                    this.unbindUI().load();
                }
            }));
        });

        SlotManager.prototype.backgroundLoad = utils.fluent(function( list )
        {
            if ( utils.isEmptyArray(list) ) return;

            list.forEach(function( photo )
            {
                setTimeout(function( url )
                {
                    new Image().src = url;
                }, 1, photo.url);
            })
        });

        SlotManager.prototype.reset = utils.fluent(function()
        {
            this.data = this.uniqueList = null;
            this.offset = 0;
            this.isCompleted = false;
            this.cache = {};
        });

        SlotManager.prototype.getSlot = function( slotId )
        {
            return this.cache[slotId] || null;
        };

        SlotManager.prototype.updateSlot = utils.fluent(function( slotId, data )
        {
            if ( !this.cache.hasOwnProperty(slotId) ) return;

            this.cache[slotId].setInfo(data);
        });

        SlotManager.prototype.updateRate = utils.fluent(function( data )
        {
            var keys = ['entityId', 'userScore', 'avgScore', 'ratesCount'];
            var values = utils.getObjectValue(keys, data);

            if ( keys.length !== Object.keys(values).length ) return;

            this.updateSlot(values.entityId, {
                userScore: values.userScore,
                rateInfo: values
            });
        });

        SlotManager.prototype.updateAlbumPhotos = utils.fluent(function( photoId )
        {
            if ( photoId === undf || !this.cache.hasOwnProperty(photoId) ) return;

            var slot = this.cache[photoId];
            var albumId = slot.data.albumId;
            var slots = Object.keys(this.cache).reduce(this.self(function( result, slotId )
            {
                var slot = this.cache[slotId];

                if ( slot.data.albumId == albumId )
                {
                    result.push(slot.data.id);
                }

                return result;
            }), []);

            PEEP.trigger('photo.deleteCache', [slots]);

            this.getPhotoInfo(albumId, slots, this.self(function( data, textStatus, jqXHR )
            {
                if ( data && data.status === 'success' )
                {
                    utils.includeScriptAndStyle(data.scripts);
                    this.rebuildSlots(data.data);
                }
                else
                {
                    PEEP.error('Server error');
                }
            }));
        });

        SlotManager.prototype.getPhotoInfo = utils.fluent(function( albumId, photos, then )
        {
            $.ajax({
                url: params.getPhotoURL,
                dataType: 'json',
                data: {
                    ajaxFunc: 'getPhotoInfo',
                    albumId: albumId,
                    photos: photos
                },
                cache: false,
                type: 'POST',
                success: then,
                error: function( jqXHR, textStatus, errorThrown )
                {
                    throw textStatus;
                }
            });
        });

        SlotManager.prototype.rebuildSlots = utils.fluent(function( data )
        {
            if ( !data || utils.isEmptyArray(data.photoList) ) return;

            this.data = data;
            this.uniqueList = data.unique;
            this.backgroundLoad(data.photoList);

            this.data.photoList.forEach(function( photo )
            {
                if ( !this.cache.hasOwnProperty(photo.id) ) return;

                var slot = this.cache[photo.id];

                $.extend(slot.data, photo);
                this.updateSlot(photo.id, this.getSlotData(photo));
                PEEP.trigger('photo.onRenderPhotoItem', [photo], slot.node);

                if ( params.classicMode )
                {
                    slot.node.find('.peep_photo_item').css('background-image', 'url(' + slot.data.url + ')');
                    slot.node.find('img.peep_hidden').attr('src', slot.data.url);
                }
                else
                {
                    var img = slot.node.find('img').show()[0];

                    img.onload = img.onerror = this.self(function()
                    {
                        this.list.reorder();
                    });
                    img.src = slot.data.url;
                }

            }, this);
        });

        return SlotManager;
    })(BaseObject);

    var List = (function( BaseObject )
    {
        var instance, SLOT_OFFSET = 16;

        utils.extend(List, BaseObject);

        function List( slotManager )
        {
            if ( instance !== undf )
            {
                return instance;
            }

            if ( !(this instanceof List) )
            {
                return new List(slotManager);
            }

            List._super.call(this);

            this.slotManager = slotManager;

            this.content = $('#browse-photo');
            this.loader = $('#browse-photo-preloader');
            this.reset();

            instance = this;
        }

        List.prototype.showLoader = utils.fluent(function()
        {
            this.loader.insertAfter(this.content);
        });

        List.prototype.hideLoader = utils.fluent(function()
        {
            this.loader.detach();

            if ( this.content.is(':empty') )
            {
                this.content.append(
                    $('<div>', {style: 'text-align:center; padding-top: 24px;'}).html(PEEP.getLanguageText('photo', 'no_items'))
                );
            }
        });

        List.prototype.buildByMode = (function()
        {
            if ( params.classicMode )
            {
                return function( slot )
                {
                    slot.node.find('.peep_photo_item').css('background-image', 'url(' + slot.data.url + ')');
                    slot.node.find('img.peep_hidden').attr('src', slot.data.url);
                    slot.node.appendTo(this.content);
                    slot.node.fadeIn(100, this.self(function()
                    {
                        this.slotManager.buildNewOne();
                    }));
                };
            }
            else
            {
                if ( params.listType == 'albums' )
                {
                    return function( slot )
                    {
                        var img = slot.node.find('img')[0];

                        img.onerror = img.onload = this.self(function(){this.buildComplete(slot)});
                        img.src = slot.data.url;
                        slot.node.appendTo(this.content);
                    };
                }

                return function( slot )
                {
                    var offset = this.getOffset();

                    slot.node.css({
                        top: offset.top,
                        left: offset.left / (params.level || 4) * 100 + '%'
                    });
                    slot.node.find('.peep_photo_pint_album img').attr('src', slot.data.url);
                    slot.node.appendTo(this.content);
                    this.content.height(Math.max.apply(Math, this.photoListOrder));

                    var img = new Image();
                    img.onload = img.onerror = this.self(function(){this.buildComplete(slot)});
                    img.src = slot.data.url;
                };
            }
        })();

        List.prototype.buildComplete = utils.fluent(function( slot )
        {
            slot.node.fadeIn(100, this.self(function()
            {
                if ( slot.data.unique !== this.slotManager.uniqueList )
                {
                    return;
                }

                var offset = this.getOffset();

                this.photoListOrder[offset.left] += slot.node.height() + SLOT_OFFSET;
                this.content.height(Math.max.apply(Math, this.photoListOrder));
                this.slotManager.buildNewOne();
            }));
        });

        List.prototype.reorder = utils.fluent(function()
        {
            if ( params.classicMode ) return;

            this.photoListOrder = this.photoListOrder.map(Number.prototype.valueOf, 0);

            $('.peep_photo_item_wrap', this.content).each(this.self(function( index, node )
            {
                var self = $(node), offset = this.getOffset();

                self.css({top: offset.top + 'px', left: offset.left / (params.level || 4) * 100 + '%'});
                this.photoListOrder[offset.left] += self.height() + SLOT_OFFSET;
            }));

            this.content.height(Math.max.apply(Math, this.photoListOrder));
        });

        List.prototype.reset = utils.fluent(function()
        {
            this.photoListOrder = Array.apply(Array, Array(params.level || 4)).map(Number.prototype.valueOf, 0);
            this.content.hide().empty().css('height', 'auto').show();
        });

        List.prototype.getOffset = function()
        {
            var top = Math.min.apply(Math, this.photoListOrder);
            var left = this.photoListOrder.indexOf(top);

            return {
                top: top,
                left: left
            };
        };

        return List;
    })(BaseObject);

    var SearchResultItem = (function( BaseObject )
    {
        utils.extend(SearchResultItem, BaseObject);

        function SearchResultItem( searchEngine, type )
        {
            if ( !(this instanceof SearchResultItem) )
            {
                return new SearchResultItem(searchEngine, type);
            }

            SearchResultItem._super.call(this);

            this.searchEngine = searchEngine;
            this.data = {};

            switch ( type )
            {
                case 'user':
                    this.node = searchEngine.userItemPrototype.clone();
                    break;
                default:
                    this.node = searchEngine.hashItemPrototype.clone();
                    break;
            }
        }

        SearchResultItem.prototype.setSearchResultItemInfo = utils.fluent(function()
        {
            var reg, searchVal = this.getData('searchVal'), label = this.getData('label');

            switch ( this.getData('searchType') )
            {
                case 'user':
                    reg = new RegExp(searchVal.substring(1), 'i');

                    this.node.find('img').attr('src', this.getData('avatar'));
                    this.node.find('.peep_searchbar_username').html(label.replace(reg, function( p1 )
                    {
                        return '<b>' + p1 + '</b>';
                    }));
                    break;
                case 'hash':
                    reg = new RegExp(searchVal.substring(1), 'gi');

                    this.node.find('.peep_search_result_tag').html(label.replace(reg, function( p1 )
                    {
                        return '<b>' + p1 + '</b>';
                    }));
                    break;
                case 'desc':
                    reg = new RegExp(searchVal, 'gi');

                    this.node.find('.peep_search_result_tag').html(label.replace(reg, function( p1 )
                    {
                        return '<b>' + p1 + '</b>';
                    }));
                    break;
            }

            this.node.find('.peep_searchbar_ac_count').html(this.getData('count'));
            this.node.on('click', this.self(function()
            {
                this.searchEngine.getSearchResultPhotos(this);
            }));
        });

        SearchResultItem.prototype.setData = utils.fluent(function( data )
        {
            $.extend(this.data, data);
        });

        SearchResultItem.prototype.getData = function( key )
        {
            return this.data[key] || null;
        };

        return SearchResultItem;
    })(BaseObject);

    var SearchEngine = (function( BaseObject )
    {
        utils.extend(SearchEngine, BaseObject);

        var timerId, instance;

        function SearchEngine()
        {
            if ( !(this instanceof SearchEngine) )
            {
                return new SearchEngine();
            }

            if ( instance !== undf )
            {
                return instance;
            }

            SearchEngine._super.call(this);

            this.searchBox = document.getElementById('photo-list-search');
            this.searchResultList = $('.peep_searchbar_ac', this.searchBox);
            this.hashItemPrototype = $('li.hash-prototype', this.searchBox).removeClass('hash-prototype');
            this.userItemPrototype = $('li.user-prototype', this.searchBox).removeClass('user-prototype');
            this.searchInput = $('input:text', this.searchBox);
            this.listBtns = $('.peep_fw_btns > a');
            instance = this;
        }

        SearchEngine.prototype.init = utils.fluent(function()
        {
            this.searchInput.on({
                keyup: this.self(function( event )
                {
                    timerId && (clearTimeout(timerId), this.abortSearchRequest(), timerId = null);

                    if ( event.keyCode === 13 )
                    {
                        this.destroySearchResultList().searchAll(this.searchInput.val());
                    }
                    else
                    {
                        timerId = setTimeout(function()
                        {
                            this.search(this.searchInput.val());
                        }.bind(this), 300);
                    }
                }),
                focus: this.self(function()
                {
                    if ( !this.searchResultList.is(':empty') )
                    {
                        this.searchResultList.show();
                    }
                })
            });

            this.listBtns.on('click', this.self(function( event )
            {
                event.preventDefault();
                params['listType'] = event.currentTarget.getAttribute('list-type');
                this.loadList(params['listType']);
            }));

            $('.peep_btn_close_search', this.searchBox).on('click', this.self(function()
            {
                this.loadList(this.searchInitList);
            }));

            $('.peep_searchbar_btn', this.searchBox).on('click', this.self(function( event )
            {
                var value = this.searchInput.val().trim();

                if ( value.length === 0 || value === PEEP.getLanguageText('photo', 'search_invitation') )
                {
                    event.stopPropagation();

                    return;
                }

                this.abortSearchRequest().searchAll(value);
            }));

            $(document).on('click', this.self(function( event )
            {
                if ( event.target.id === 'search-photo' )
                {
                    event.stopPropagation();
                }
                else if ( this.searchResultList.is(':visible') )
                {
                    this.searchResultList.hide();
                }
            }));
        });

        SearchEngine.prototype.search = utils.fluent(function( searchVal )
        {
            searchVal = searchVal.trim();

            if ( searchVal.length <= 2 || searchVal === this.preSearchVal )
            {
                return;
            }

            this.request = $.ajax(
            {
                url: params.getPhotoURL,
                dataType: 'json',
                data: {
                    ajaxFunc: 'getSearchResult',
                    searchVal: searchVal
                },
                cache: false,
                type: 'POST',
                beforeSend: this.self(function( jqXHR, settings )
                {
                    this.preSearchVal = searchVal;
                    this.destroySearchResultList().showSearchProcess();
                }),
                success: this.self(function( data, textStatus, jqXHR )
                {
                    if ( data && data.result )
                    {
                        this.buildSearchResultList(data.type, data.list, searchVal, data.avatarData);
                    }
                    else
                    {
                        PEEP.error('Server error');
                    }
                }),
                error: function( jqXHR, textStatus, errorThrown )
                {
                    throw textStatus;
                },
                complete: function()
                {
                    timerId = null
                }
            });
        });

        SearchEngine.prototype.searchAll = utils.fluent(function( searchVal )
        {
            searchVal = searchVal.trim();

            if ( searchVal.length <= 2 || searchVal === this.preAllSearchVal )
            {
                return;
            }

            this.preAllSearchVal = searchVal;
            this.getSearchResultPhotos('all');
        });

        SearchEngine.prototype.buildSearchResultList = utils.fluent(function( type, list, searchVal, avatarData )
        {
            this.hideSearchProcess();

            var keys;

            if ( (keys = Object.keys(list)).length === 0 )
            {
                $('#search-no-items').clone().removeAttr('id').appendTo(this.searchResultList);

                return;
            }

            this.searchVal = searchVal;
            this.changeListType(type);

            keys.forEach(function( item )
            {
                var data = list[item];
                var searchItem = new SearchResultItem(this, this.searchType);

                searchItem.setData({
                    searchType: this.searchType,
                    searchVal: searchVal,

                    id: data.id,
                    label: data.label,
                    avatar: avatarData !== undf ? avatarData[data.id].src : null,
                    count: data.count
                });
                searchItem.setSearchResultItemInfo();

                searchItem.node.appendTo(this.searchResultList).slideDown(200);
            }, this);
        });

        SearchEngine.prototype.changeListType = utils.fluent(function( type )
        {
            PEEP.trigger('photo.onChangeListType', [type]);

            if ( this.searchType === type ) return;

            this.searchType = type;

            if ( ['user', 'hash', 'desc', 'all'].indexOf(type) === -1 )
            {
                root.history.replaceState(null, null, type);
                root.document.title = PEEP.getLanguageText('photo', 'meta_title_photo_' + type);
                this.searchInput.val('');
            }
        });

        SearchEngine.prototype.getSearchResultPhotos = utils.fluent(function( mixed )
        {
            this.resetPhotoListData();
            $('.peep_searchbar_input', this.searchBox).addClass('active');

            if ( mixed instanceof SearchResultItem )
            {
                this.changeListType(mixed.getData('searchType'));
                this.preAllSearchVal = '';
            }
            else
            {
                this.changeListType(mixed);
            }

            if ( !this.searchInitList )
            {
                this.searchInitList = SlotManager().listType;
            }

            var data = {
                listType: this.searchType
            };


            switch ( this.searchType )
            {
                case 'desc':
                    data.searchVal = mixed.getData('searchVal');
                case 'user':
                case 'hash':
                    data.id = mixed.getData('id');
                    break;
                case 'all':
                    data.searchVal = this.searchInput.val().trim();
                    data.ajaxFunc = 'getSearchAllResult';
                    break;
            }

            this.searchData = data;
            SlotManager().load(data);
        });

        SearchEngine.prototype.loadList = utils.fluent(function( listType )
        {
            this.resetPhotoListData();

            $('.peep_searchbar_input', this.searchBox).removeClass('active');
            this.listBtns.filter('[list-type="' + listType + '"]').addClass('active');

            this.searchInitList = listType;
            this.changeListType(listType);
            this.destroySearchResultList();
            SlotManager().load({listType: listType});
        });

        SearchEngine.prototype.getSearchValue = function()
        {
            return this.searchVal || this.searchInput.val().trim();
        };

        SearchEngine.prototype.resetPhotoListData = utils.fluent(function()
        {
            this.listBtns.removeClass('active');

            SlotManager().reset();
            List().reset();
        });

        SearchEngine.prototype.abortSearchRequest = utils.fluent(function()
        {
            if ( this.request && this.request.readyState !== 4 )
            {
                try
                {
                    this.request.abort();
                }
                catch ( ignore ) { }
            }
        });

        SearchEngine.prototype.destroySearchResultList = utils.fluent(function()
        {
            this.searchResultList.hide().empty();
        });

        SearchEngine.prototype.showSearchProcess = utils.fluent(function()
        {
            this.searchResultList.append($('<li>').addClass('browse-photo-search clearfix peep_preloader')).show();
        });

        SearchEngine.prototype.hideSearchProcess = utils.fluent(function()
        {
            this.searchResultList.find('.peep_preloader').detach();
        });

        SearchEngine.prototype.getSearchData = function()
        {
            return this.searchData || {};
        };

        return SearchEngine;
    })(BaseObject);

    return root.Object.freeze({
        init: function()
        {
            var slotManager = new SlotManager();

            slotManager.load();

            if ( ['albums', 'userPhotos', 'albumPhotos'].indexOf(params.listType) === -1 )
            {
                var searchEngine = new SearchEngine();

                searchEngine.init();
            }

            PEEP.bind('photo.onSetRate', function( data )
            {
                slotManager.updateRate(data);
            });

            PEEP.bind('photo.updateAlbumPhotos', function( photoId )
            {
                SlotManager().updateAlbumPhotos(photoId);
            });

            var updateCommentCount = function( data )
            {
                slotManager.updateSlot(data.entityId, data);
            };

            PEEP.bind('base.comment_delete', updateCommentCount);
            PEEP.bind('base.comment_added', updateCommentCount);
            PEEP.bind('photo.onBeforeLoadFromCache', function()
            {
                PEEP.bind('base.comment_delete', updateCommentCount);
                PEEP.bind('base.comment_added', updateCommentCount);
            });
        },
        getMoreData: function()
        {
            return SlotManager().getMoreData();
        },
        reorder: function()
        {
            List().reorder();
        },
        removePhotoItems: function( idList )
        {
            SlotManager().removePhotoItems(idList);
        },
        updateSlot: function( slotId, data )
        {
            PEEP.trigger('photo.onUpdateSlot', [slotId, data]);

            SlotManager().updateSlot(slotId, data);
        },
        getSlot: function( slotId )
        {
            return SlotManager().getSlot(slotId);
        },
        getListData: function()
        {
            return $.extend({}, SearchEngine().getSearchData(), SlotManager().getMoreData());
        }
    });
}));

(function( root, $ )
{
    var _params = {};
    var _contextList = [];
    var _methods = {
        sendRequest: function( ajaxFunc, entityId, success )
        {
            $.ajax(
            {
                url: _params.actionUrl,
                type: 'POST',
                cache: false,
                data:
                {
                    ajaxFunc: ajaxFunc,
                    entityId: entityId
                },
                dataType: 'json',
                success: success,
                error: function( jqXHR, textStatus, errorThrown )
                {
                    PEEP.error(textStatus);

                    throw textStatus;
                }
            });
        },
        deletePhoto: function( slot )
        {
            if ( !confirm(PEEP.getLanguageText('photo', 'confirm_delete')) )
            {
                return false;
            }

            var photoId = slot.id;

            _methods.sendRequest('ajaxDeletePhoto', photoId, function( data )
            {
                if ( window.hasOwnProperty('photoAlbum') )
                {
                    photoAlbum.setCoverUrl(data.coverUrl, data.isHasCover)
                }
                
                if ( data.result === true )
                {
                    PEEP.info(data.msg);

                    browsePhoto.removePhotoItems([photoId]);
                } 
                else if ( data.hasOwnProperty('error') )
                {
                    PEEP.error(data.error);
                }
            });
        },
        editPhoto: function( slot )
        {
            var photoId = slot.id;
            var editFB = PEEP.ajaxFloatBox('PHOTO_CMP_EditPhoto', {photoId: photoId}, {iconClass: 'peep_ic_edit', title: PEEP.getLanguageText('photo', 'tb_edit_photo'),
                onLoad: function()
                {
                    peepForms['photo-edit-form'].bind("success", function( data )
                    {
                        editFB.close();
                        
                        if ( data && data.result )
                        {
                            if ( data.photo.status !== 'approved' )
                            {
                                PEEP.info(data.msgApproval);
                                browsePhoto.removePhotoItems([photoId]);
                            }
                            else
                            {
                                PEEP.info(data.msg);
                                browsePhoto.updateSlot(data.id, data);
                            }

                            browsePhoto.reorder();
                            
                            PEEP.trigger('photo.onAfterEditPhoto', [data.id]);
                        }
                        else if ( data.msg )
                        {
                            PEEP.error(data.msg);
                        }
                    });
                }}
            );
        },
        saveAsAvatar: function( slot )
        {
            document.avatarFloatBox = PEEP.ajaxFloatBox(
                "BASE_CMP_AvatarChange",
                { params : { step: 2, entityType : 'photo_album', entityId : '', id : slot.id } },
                { width : 749, title : PEEP.getLanguageText('base', 'avatar_change') }
            );
        },
        saveAsCover: function( slot )
        {
            var photoId = slot.id;
            var img, item = $('#photo-item-' + photoId), dim;
            
            if ( _params.isClassic )
            {
                img = $('img.peep_hidden', item)[0];
            }
            else
            {
                img = $('img', item)[0];
            }
            
            if ( slot.dimension && slot.dimension.length )
            {
                try
                {
                    var dimension = JSON.parse(slot.dimension);

                    dim = dimension.main;
                }
                catch( e )
                {
                    dim = [img.naturalWidth, img.naturalHeight];
                }
            }
            else
            {
                dim = [img.naturalWidth, img.naturalHeight];
            }

            if ( dim[0] < 330 || dim[1] < 330 )
            {
                PEEP.error(PEEP.getLanguageText('photo', 'to_small_cover_img'));

                return;
            }
    
            window.albumCoverMakerFB = PEEP.ajaxFloatBox('PHOTO_CMP_MakeAlbumCover', [_params.albumId, photoId], {
                title: PEEP.getLanguageText('photo', 'set_as_album_cover'),
                width: '700',
                onLoad: function()
                {
                    window.albumCoverMaker.init();
                }
            });
        },
        editAlbum: function( slot )
        {
            window.location = slot.albumUrl + '#edit';
        },
        deleteAlbum: function( album )
        {
            if ( !confirm(PEEP.getLanguageText('photo', 'are_you_sure')) )
            {
                return;
            }

            _methods.sendRequest('ajaxDeletePhotoAlbum', album.id, function( data )
            {
                if ( data.result )
                {
                    PEEP.info(data.msg);

                    browsePhoto.removePhotoItems([album.id]);
                }
                else
                {
                    if ( data.msg )
                    {
                        PEEP.error(data.msg);
                    }
                    else
                    {
                        alert(PEEP.getLanguageText('photo', 'no_photo_selected'));
                    }
                }
            });
        },
        call: function( action, slot )
        {
            if ( _methods.hasOwnProperty(action) )
            {
                _methods[action](slot);
            }
        },
        createElement: function( action, html, style )
        {
            return $('<li/>').addClass([style || '', action].join(' ')).append(
                $('<a/>', {href : 'javascript://'}).data('action', action).html(html)
            );
        },
        init: function()
        {
            $.extend(_params, (root.photoContextActionParams || {}));
            
            switch ( _params.listType )
            {
                case 'albums':
                    if ( _params.isOwner )
                    {
                        _contextList.push(_methods.createElement('editAlbum', PEEP.getLanguageText('photo', 'edit_album')));
                        _contextList.push(_methods.createElement('deleteAlbum', PEEP.getLanguageText('photo', 'delete_album'), 'delete_album'));
                    }
                    break;
                default:
                    if ( _params.downloadAccept === true )
                    {
                        var element  = _methods.createElement('downloadPhoto', PEEP.getLanguageText('photo', 'download_photo'));

                        element.find('a').attr('target', 'photo-downloader').addClass('download');
                        _contextList.push(element);
                    }

                    if ( _params.isOwner )
                    {
                        _contextList.push(_methods.createElement('deletePhoto', PEEP.getLanguageText('photo', 'delete_photo')));
                        _contextList.push(_methods.createElement('editPhoto', PEEP.getLanguageText('photo', 'tb_edit_photo')));

                        var divider = _methods.createElement('divider', '', 'peep_context_action_divider_wrap');

                        divider.find('a').addClass('peep_context_action_divider');
                        _contextList.push(divider);
                        _contextList.push(_methods.createElement('saveAsAvatar', PEEP.getLanguageText('photo', 'save_as_avatar')));

                        if ( _params.hasOwnProperty('albumId') && +_params.albumId > 0 )
                        {
                            _contextList.push(_methods.createElement('saveAsCover', PEEP.getLanguageText('photo', 'save_as_cover')));
                        }
                    }
                    else if ( _params.isModerator )
                    {
                        _contextList.push(_methods.createElement('deletePhoto', PEEP.getLanguageText('photo', 'delete_photo')));
                        _contextList.push(_methods.createElement('editPhoto', PEEP.getLanguageText('photo', 'tb_edit_photo')));
                    }
                    break;
            }
            
            var event = {buttons: [], actions: {}};
            PEEP.trigger('photo.collectMenuItems', [event, _params.listType]);
            $.extend(_methods, event.actions);
            
            if ( _contextList.length === 0 && event.buttons.length === 0 )
            {
                return;
            }

            var list = $('<ul>').addClass('peep_context_action_list');

            _contextList.concat(event.buttons).forEach(function(item)
            {
                item.appendTo(list);
            });

            _params.contextAction = $('<div>').addClass('peep_photo_context_action').on('click', function( event )
            {
                event.stopImmediatePropagation();
            });
            _params.contextActionPrototype = $(document.getElementById('context-action-prototype')).removeAttr('id');
            _params.contextActionPrototype.find('.peep_tooltip_body').append(list);
            
            PEEP.bind('photo.onRenderPhotoItem', function( slot )
            {
                var self = $(this);
                var prototype = _params.contextActionPrototype.clone(true);

                prototype.find('.download').attr('href', _params.downloadUrl.replace(':id', slot.id));

                if ( _params.listType == 'albums' && slot.name.trim() == PEEP.getLanguageText('photo', 'cnews_album').trim() )
                {
                    prototype.find('.delete_album').remove();
                }

                prototype.find('.peep_tooltip_body a').on('click', function()
                {
                    var action = $(this).data('action');

                    _methods.call(action, slot);
                });

                PEEP.trigger('photo.contextActionReady', [prototype, slot]);

                if ( prototype.find('li').length === 0 ) return;

                var contextAction = _params.contextAction.clone(true);
                contextAction.append(prototype);
                
                if ( _params.isClassic )
                {
                    self.find('.peep_photo_item').prepend(contextAction);
                }
                else
                {
                    self.find('.peep_photo_pint_album').append(contextAction);
                }
            });
        }
    };
    
    root.photoContextAction = Object.freeze({
        init: _methods.init,
        createElement: _methods.createElement
    });
    
})( window, jQuery );
