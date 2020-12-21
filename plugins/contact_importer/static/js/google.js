CI_GoogleLuncher = function(params)
{
    this.request = function()
    {
        window.open(params.popupUrl, 'CONTACTIMPORTER_Google', 'status=1,toolbar=0,width=550,height=700');
    };

    this.send = function(rsp, contacts, message)
    {
        var request = JSON.stringify({contacts: contacts, message: message});
        $.post(rsp, {request: request}, function(msg) {
            PEEP.info(msg);
        });
    };
};
