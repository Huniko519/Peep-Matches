
var PEEP_GoogleMapLocationAutocomplete = function ($)
{
    return function(fieldId, params) {
        var input = $('#'+fieldId);
        
        if ( input.lenght == 0 )
        {
            return;
        }
        
        var data = $('#'+fieldId).autocomplete(params).data("ui-autocomplete");
        
        if ( !data )
        {
            return;
        }        
        
        data._resizeMenu = function() {
            this.menu.element.outerWidth( $('#' + fieldId).outerWidth() );
            $(this.menu.element).addClass("googlelocation_autocomplite_menu");
        };

        var func = data._renderItem;
        data._renderItem = function( ul, item ) {
            var element = func(ul, item);
            element.find("a").prepend("<span class='ic_googlelocation_menu_item_pin'>");
            return element;
        };
        
        return data;
    }
}(locationJquey);
