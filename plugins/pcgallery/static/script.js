PCGALLERY = (function() {
    var MAX_PHOTOS = 4, CHANGE_SPEED = 3000;
    var _container, _settings, _photos = [], _changeOrder = 1, _photoIndex = MAX_PHOTOS;
    var delayChange, changeNextImage, getNextPhoto, isPhotoVisible, loadImage, showPhoto;

    isPhotoVisible = function( photoId ) {
        return $('.pcg-image', _container).filter(function() {
            return $(this).data("pid") == photoId;
        }).length > 0;
    };

    getNextPhoto = function() {
        var photo = _photos[_photoIndex];

        _photoIndex++;
        _photoIndex = _photoIndex >= _photos.length ? 0 : _photoIndex;

        if ( photo && isPhotoVisible(photo.id) ) {
            return getNextPhoto();
        }

        return photo;
    };

    delayChange = function() {
        window.setTimeout(changeNextImage, _settings.changeInterval);
    };

    loadImage = function( src, callBack ) {
        $('<img>').attr("src", src).load(function() {
            callBack(src);
        });
    };

    changeNextImage = function() {
        var image, newImage,photo = getNextPhoto();
        if ( !photo ) return;

        image = $('.pcg-image[data-order=' + _changeOrder + ']', _container);

        loadImage(photo.src, function() {
            newImage = image.clone();

            newImage.css("background-image", 'url(' + photo.src + ')');
            newImage.css("opacity", 0);
            newImage.data("pid", photo.id);

            image.after(newImage);

            newImage.animate({opacity: 1}, CHANGE_SPEED, delayChange);

            image.animate({opacity: 0}, CHANGE_SPEED, function() {
                image.remove();
            });
        });

        _changeOrder = _changeOrder >= MAX_PHOTOS ? 1 : _changeOrder + 1;
    };

    showPhoto = function( photoId ) {
        window.photoView.setId(photoId, _settings.listType);
    };
    
    function showSettings()
    {
        var scope = {};
        
        PEEP.ajaxFloatBox("PCGALLERY_CMP_GallerySettings", [_settings.userId], {
            title: PEEP.getLanguageText("pcgallery", "setting_fb_title"),
            scope: scope
        });
    }

    return {
        init: function( uniqId, settings, photos ) {
            _container = $('#' + uniqId);
            _settings = settings;
            _photos = photos;

            delayChange();

            $(document).on("click", ".pcg-image", function() {
                showPhoto($(this).data("pid"));
            });
                        
            $("#pcgallery-settings-btn").click(showSettings);
        }
    };
})();