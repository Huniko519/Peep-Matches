(function( $ )
{
    var _vars = $.extend({}, (albumParams || {}), {albumNameList: [], photoIdList: [], mode: 'view'}),
    _elements = {},
    _methods = {
        setEditMode: function()
        {
            _vars.photoIdList.length = 0;
            _vars.mode = 'edit';
            
            $('.peep_photo_item_wrap', $(document.getElementById('browse-photo')).addClass('peep_photo_edit_mode')).each(function()
            {
                _methods.converPhotoItemToEditMode.call(this);
            });
            
            _elements.editBtn.detach();
            _elements.doneCont.appendTo($('.peep_photo_album_toolbar', _elements.editCont)).show();
            _elements.editForm.appendTo(_elements.albumInfo).show();
            _elements.coverBtn.appendTo($('.peep_photo_album_cover', _elements.albumInfo)).show();
            _elements.menu.insertAfter(_elements.editCont).show();

            if ( _vars.album.name != PEEP.getLanguageText('photo', 'cnews_album').trim() )
            {
                _elements.editCont.addClass('peep_photo_album_edit');
            }
            else
            {
                _elements.editCont.find('.peep_photo_album_description').hide();
                _elements.editCont.find('.peep_photo_album_description_textarea').show();
            }

            PEEP.trigger('photo.albumEditClick');
        },
        setViewMode: function()
        {
            try
            {
                peepForms.albumEditForm.removeErrors();
                peepForms.albumEditForm.validate();
            }
            catch ( e )
            {
                return;
            }
            
            if ( _vars.albumNameList.indexOf(peepForms.albumEditForm.elements.albumName.getValue().trim()) !== -1 )
            {
                PEEP.error(PEEP.getLanguageText('photo', 'album_name_error'));
                
                return;
            }
            
            $('.peep_photo_item_wrap', $(document.getElementById('browse-photo')).removeClass('peep_photo_edit_mode')).each(function()
            {
                _methods.converPhotoItemToViewMode.call(this);
            });

            peepForms.albumEditForm.submitForm();
            history.pushState(null, document.title, window.location.pathname);
            _vars.photoIdList.length = 0;
            _vars.mode = 'view';
            
            $('.set_as_cover', _elements.menu).addClass('peep_bl_disabled').off();

            _elements.doneCont.detach();
            _elements.coverBtn.detach();
            _elements.editBtn.appendTo($('.peep_photo_album_toolbar', _elements.editCont));
            _elements.menu.detach();
            _elements.editForm.detach();
            _elements.albumInfo.find('.peep_photo_album_name').html(peepForms.albumEditForm.elements.albumName.getValue());
            _elements.albumInfo.find('.peep_photo_album_description').html(peepForms.albumEditForm.elements.desc.getValue());

            if ( _vars.album.name != PEEP.getLanguageText('photo', 'cnews_album').trim() )
            {
                _elements.editCont.removeClass('peep_photo_album_edit');
            }
            else
            {
                _elements.editCont.find('.peep_photo_album_description').show();
                _elements.editCont.find('.peep_photo_album_description_textarea').hide();
            }
            
            PEEP.info(PEEP.getLanguageText('photo', 'photo_album_updated'));
        },
        converPhotoItemToEditMode: function()
        {
            var self = $(this);
            
            if ( _elements.selectAll[0].checked )
            {
                self.find('.peep_photo_item').addClass('peep_photo_item_checked');
                _vars.photoIdList.push(+self.data('slotId'));
            }
            
            self.find('img:first').after(_elements.checkbox.clone().on('click', function()
            {
                var closest = $(this).closest('.peep_photo_item');
                
                if ( closest.hasClass('peep_photo_item_checked') )
                {
                     closest.removeClass('peep_photo_item_checked');
                     _vars.photoIdList.splice(_vars.photoIdList.indexOf(+closest.parent().data('slotId')), 1);
                }
                else
                {
                    closest.addClass('peep_photo_item_checked');
                     _vars.photoIdList.push(+closest.parent().data('slotId'));
                }
                
                _vars.photoIdList.length === 1 ? $('.set_as_cover', _elements.menu).removeClass('peep_bl_disabled').on('click', _methods.makeAsCover) : $('.set_as_cover', _elements.menu).addClass('peep_bl_disabled').off();
            }));
        },
        converPhotoItemToViewMode: function()
        {
            $(this).find('.peep_photo_item').removeClass('peep_photo_item_checked').find('.peep_photo_chekbox_area').remove();
        },
        makeAsCover: function( event )
        {
            event.stopImmediatePropagation();
            
            var img, item = document.getElementById('photo-item-' + _vars.photoIdList[0]), dim;
            
            if ( _vars.isClassic )
            {
                img = $('img.peep_hidden', item)[0];
            }
            else
            {
                img = $('img', item)[0];
            }

            var slot = browsePhoto.getSlot(_vars.photoIdList[0]);
            
            if ( slot.data.dimension && slot.data.dimension.length )
            {
                try
                {
                    var dimension = JSON.parse(slot.data.dimension);

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
    
            window.albumCoverMakerFB = PEEP.ajaxFloatBox('PHOTO_CMP_MakeAlbumCover', [_vars.album.id, _vars.photoIdList[0]], {
                title: PEEP.getLanguageText('photo', 'set_as_cover_label'),
                width: '700',
                onLoad: function()
                {
                    window.albumCoverMaker.init();
                }
            });
        },
        checkPhotoIsSelected: function()
        {
            if ( _vars.photoIdList.length === 0 )
            {
                alert(PEEP.getLanguageText('photo', 'no_photo_selected'));

                return false;
            }
            
            return true;
        },
        createNewAlbumAndMove: function()
        {
            var fb = PEEP.ajaxFloatBox('PHOTO_CMP_CreateAlbum', [_vars.album.id, _vars.photoIdList.join(',')], {
                title: PEEP.getLanguageText('photo', 'move_to_new_album'),
                width: '500',
                onLoad: function()
                {
                    peepForms['add-album'].bind('success', function( data )
                    {
                        fb.close();

                        _methods.movePhotoSuccess(data);
                    });
                }
            });
        },
        movePhoto: function()
        {
            $('.peep_context_action_list li', _elements.menu).slice(1).remove();
            
            $.ajax({
                url: _vars.url,
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: 
                {
                    "ajaxFunc": 'ajaxMoveToAlbum',
                    "from-album": _vars.album.id,
                    "to-album": $(this).attr('rel'),
                    "photos": _vars.photoIdList.join(','),
                    "album-name": $(this).html()
                },
                success: _methods.movePhotoSuccess,
                error: function( jqXHR, textStatus, errorThrown )
                {
                    PEEP.error(textStatus);

                    throw textStatus;
                }
            });
        },
        movePhotoSuccess: function( data )
        {
            if ( data.result )
            {
                PEEP.info(PEEP.getLanguageText('photo', 'photo_success_moved'));
                $('.peep_photo_album_cover', _elements.albumInfo).css('background-image', 'url(' + data.coverUrl + ')');
                
                if ( data.isHasCover === false )
                {
                    _elements.coverBtn.remove();
                    _elements.coverBtn = $();
                }
                
                if ( !$.isEmptyObject(data.albumNameList) )
                {
                    _vars.albumNameList.length = 0;
                    $('.peep_context_action_list li', _elements.menu).slice(1).remove();
                    
                    var li = document.createElement('li');
                    li.appendChild((function()
                    {
                        var div = document.createElement('div');
                        div.className = 'peep_console_divider';
                        return div;
                    })());
                    
                    var list = $('ul.peep_context_action_list', _elements.menu);
                    list.append(li);
                    
                    $.each(data.albumNameList, function( id, albumName )
                    {
                        _vars.albumNameList.push(albumName);
                        
                        var li = document.createElement('li');
                        li.appendChild((function()
                        {
                            var a = document.createElement('a');
                            a.setAttribute('href', 'javascript://');
                            a.setAttribute('rel', id);
                            a.appendChild(document.createTextNode(albumName));
                            $(a).on('click', function()
                            {
                                if ( _methods.checkPhotoIsSelected() )
                                {
                                    _methods.movePhoto.call(this);
                                }
                            });
                            
                            return  a;
                        })());
                        
                        list.append(li);
                    });
                }
                
                browsePhoto.removePhotoItems(_vars.photoIdList);
                
                _vars.photoIdList.length = 0;
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
        },
        init: function()
        {
            PEEP.bind('photo.onRenderPhotoItem', function()
            {
                if ( _vars.mode !== 'edit' || $('.peep_photo_chekbox_area', this).length !== 0 )
                {
                    return;
                }
                
                _methods.converPhotoItemToEditMode.call(this);
            });
            PEEP.bind('photo.afterPhotoEdit', function( data )
            {
                if ( data && data.albumName && _vars.album.name.trim() != data.albumName.trim() )
                {
                    window.browsePhoto.removePhotoItems([data.id]);
                }
            });
            
            _elements.checkbox = $((function()
            {
                var e = document.createElement('div');
                e.className = 'peep_photo_chekbox_area';
                e.appendChild((function()
                {
                    var e = document.createElement('div');
                    e.className = 'peep_photo_checkbox';

                    return e;
                })());

                return e;
            })());

            _elements.editCont = $(document.getElementById('album-edit'));
            _elements.albumInfo = $('.peep_photo_album_info', _elements.editCont);
            _elements.editForm = $('form', _elements.albumInfo);
            (_elements.editBtn = $('.edit_btn', _elements.editCont)).find('a').on('click', _methods.setEditMode);
            _elements.coverBtn = $('.peep_lbutton', _elements.albumInfo).on('click', function()
            {
                var img = $('img.cover_orig', _elements.editCont)[0];

                if ( img.naturalHeight < 330 || img.naturalWidth < 330 )
                {
                    PEEP.error(PEEP.getLanguageText('photo', 'to_small_cover_img'));

                    return;
                }
                
                window.albumCoverMakerFB = PEEP.ajaxFloatBox('PHOTO_CMP_MakeAlbumCover', [_vars.album.id], {
                    title: PEEP.getLanguageText('photo', 'crop_photo_title'),
                    width: '700',
                    onLoad: function()
                    {
                        window.albumCoverMaker.init();
                    }
                });
            }).detach();
            _elements.albumCropBtn = $('#album-crop-btn').on('click', _methods.saveCover);

            (_elements.doneCont = $('.edit_done', _elements.editCont).detach()).find('.done').on('click', _methods.setViewMode);
            _elements.doneCont.find('.delete_album').on('click', function()
            {
                if ( !confirm(PEEP.getLanguageText('photo', 'are_you_sure')) )
                {
                    return;
                }

                $.ajax({
                    url: _vars.url,
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: 
                    {
                        ajaxFunc: 'ajaxDeletePhotoAlbum',
                        entityId: _vars.album.id
                    },
                    success: function( data )
                    {
                        if ( data.result )
                        {
                            PEEP.info(data.msg);
                            window.location = data.url;
                        }
                        else
                        {
                            alert(PEEP.getLanguageText('photo', 'no_photo_selected'));
                        }
                    },
                    error: function( jqXHR, textStatus, errorThrown )
                    {
                        PEEP.error(textStatus);

                        throw textStatus;
                    }
                });
            });
            _elements.menu = $(document.getElementById('photo-menu')).detach();

            (_elements.selectAll = _elements.menu.find('input:checkbox')).on('click', function()
            {
                $('.set_as_cover', _elements.menu).addClass('peep_bl_disabled').off();
                _vars.photoIdList.length = 0;

                if ( this.checked )
                {
                    $('.peep_photo_item', document.getElementById('browse-photo')).addClass('peep_photo_item_checked');
                    $('.peep_photo_item_wrap', document.getElementById('browse-photo')).each(function()
                    {
                        _vars.photoIdList.push(+$(this).data('slotId'));
                    });

                    _vars.photoIdList.length === 1 ? $('.set_as_cover', _elements.menu).removeClass('peep_bl_disabled').on('click', _methods.makeAsCover) : $('.set_as_cover', _elements.menu).addClass('peep_bl_disabled').off();
                }
                else
                {
                    $('.peep_photo_item', document.getElementById('browse-photo')).removeClass('peep_photo_item_checked');
                }
            });

            $('.delete', _elements.menu).on('click', function()
            {
                if ( _vars.photoIdList.length === 0 )
                {
                    alert(PEEP.getLanguageText('photo', 'no_photo_selected'));

                    return;
                }

                if ( !confirm(PEEP.getLanguageText('photo', 'confirm_delete_photos')) )
                {
                    return;
                }

                $.ajax({
                    url: _vars.url,
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: 
                    {
                        ajaxFunc: 'ajaxDeletePhotos',
                        albumId: _vars.album.id,
                        photoIdList: _vars.photoIdList
                    },
                    success: function( data )
                    {
                        if ( data.result )
                        {
                            $('.peep_photo_album_cover', _elements.albumInfo).css('background-image', 'url(' + data.coverUrl + ')');
                
                            if ( data.isHasCover === false )
                            {
                                _elements.coverBtn.remove();
                                _elements.coverBtn = $();
                            }
                            
                            if ( _vars.photoIdList.length === 1 )
                            {
                                PEEP.info(PEEP.getLanguageText('photo', 'photo_deleted'));
                            }
                            else
                            {
                                PEEP.info(PEEP.getLanguageText('photo', 'photos_deleted'));
                            }

                            if ( data.url !== undefined )
                            {
                                window.location = data.url;
                            }
                            else
                            {
                                browsePhoto.removePhotoItems(_vars.photoIdList);

                                _vars.photoIdList.length = 0;
                            }
                        }
                        else
                        {
                            alert(PEEP.getLanguageText('photo', 'no_photo_selected'));
                        }
                    },
                    error: function( jqXHR, textStatus, errorThrown )
                    {
                        PEEP.error(textStatus);

                        throw textStatus;
                    }
                });
            });

            $('.peep_context_action_list a', _elements.menu)
                .on('click', function( event )
                {
                    if ( !_methods.checkPhotoIsSelected() )
                    {
                        event.stopImmediatePropagation();

                        return false;
                    }
                })
                .eq(0).on('click', _methods.createNewAlbumAndMove)
                .end().slice(1).on('click', _methods.movePhoto);

            if ( window.location.hash === '#edit' )
            {
                _methods.setEditMode();
            }
        }
    };

    window.photoAlbum = Object.defineProperties({}, {
        init: {value: _methods.init},
        setCoverUrl: {
            value: function( url, isHasCover )
            {
                $('.peep_photo_album_cover', _elements.albumInfo).css('background-image', 'url(' + url + ')');
                
                if ( isHasCover === false )
                {
                    _elements.coverBtn.remove();
                    _elements.coverBtn = $();
                }
            }
        }
    });
})(jQuery);
