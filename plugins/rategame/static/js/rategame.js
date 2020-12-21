var RategameRate = function( params ){
    this.cmpId = params.cmpId;
    this.userRate = params.userRate;
    this.entityId = params.entityId;
    this.entityType = params.entityType;
    this.itemsCount = params.itemsCount;
    this.respondUrl = params.respondUrl;
    this.ownerId = params.ownerId;
    this.$context = $('#rate_'+params.cmpId);
    this.sex = params.sex;
    this.nextPhotoUrl = params.nextPhotoUrl;
    this.refreshPhotoUrl = params.refreshPhotoUrl;
}

RategameRate.prototype = {
    init: function(){
        var self = this;
        this.setRate(this.userRate);
        for( var i = 1; i <= this.itemsCount; i++ ){
            $('#' + this.cmpId + '_rate_item_' + i).bind( 'mouseover', {
                i:i
            },
            function(e){
                self.setRate(e.data.i);
            }
            ).bind( 'mouseout',
                function(){
                    self.setRate(self.userRate);    
                }
                ).bind( 'click', {
                i:i
            },
            function(e){
                self.updateRate(e.data.i);    
            }
            );
        }
        
        $('#rategame_refresh').click(function(){
            $.ajax({
                type: 'POST',
                url: self.refreshPhotoUrl,
                data: 'sex='+self.sex,
                dataType: 'json',
                success : function(data){

                    self.refreshComponent(data);         

                },
                error : function( XMLHttpRequest, textStatus, errorThrown ){
                    alert('Ajax Error: '+textStatus+'!');
                    throw textStatus;
                }
            });
        });
    },

    setRate: function( rate ){
        for( var i = 1; i <= this.itemsCount; i++ ){
            var $el = $('#' + this.cmpId + '_rate_item_' + i); 
            $el.removeClass('active');
            if( !rate ){
                continue;
            }
            if( i <= rate ){
                $el.addClass('active');    
            }
        }
    },
    
    refreshComponent: function( data ){
        var self = this;
        
        if ( typeof data.noPhoto != 'undefined' && data.noPhoto )
        {
            $('.rategame_preview').fadeOut(500);
            $('.nophotos').fadeIn(1000);
            return;
        }
        else
        {
            self.userRate = 0;
            self.setRate(0);
            $('.total_score', self.$context).empty().append(data.totalScoreCmp);
                    
            $('.photo_preview_image').fadeOut(500, function(){
                $('.photo_preview_image').html('<img src="'+data.imagePath+'" />');
            }).fadeIn(1200);
                    
            self.entityId = data.entityId;
            self.ownerId = data.ownerId;
        }          
    },

    updateRate: function( rate ){
        var self = this;
        if( rate == this.userRate ){
            return;
        }
        this.userRateBackup = this.userRate;
        this.userRate = rate;
        $.ajax({
            type: 'POST',
            url: self.nextPhotoUrl,
            data: 'entityType='+encodeURIComponent(self.entityType)+'&entityId='+encodeURIComponent(self.entityId)+'&rate='+encodeURIComponent(rate)+'&ownerId='+encodeURIComponent(self.ownerId)+'&sex='+self.sex,
            dataType: 'json',
            success : function(data){

                if( data.errorMessage ){
                    PEEP.error(data.errorMessage);
                    self.userRate = self.userRateBackup;
                    self.setRate(self.userRateBackup);
                    return;
                }
                
                self.refreshComponent(data);

            },
            error : function( XMLHttpRequest, textStatus, errorThrown ){
                alert('Ajax Error: '+textStatus+'!');
                throw textStatus;
            }
        });
    }
}
