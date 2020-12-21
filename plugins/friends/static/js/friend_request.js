PEEP_FriendRequest = function( itemKey, params )
{
    var listLoaded = false;

    var model = PEEP.Console.getData(itemKey);
    var list = PEEP.Console.getItem(itemKey);
    var counter = new PEEP_DataModel();

    counter.addObserver(this);

    this.onDataChange = function( data )
    {
        var newCount = data.get('new');
        var counterNumber = newCount > 0 ? newCount : data.get('all');

        list.setCounter(counterNumber, newCount > 0);

        if ( counterNumber > 0 )
        {
            list.showItem();
        }
    };

    list.onHide = function()
    {
        counter.set('new', 0);
        list.getItems().removeClass('peep_console_new_message');

        model.set('counter', counter.get());
    };

    list.onShow = function()
    {
        if ( counter.get('all') <= 0 )
        {
            this.showNoContent();

            return;
        }

        if ( counter.get('new') > 0 || !listLoaded )
        {
            this.loadList();
            listLoaded = true;
        }
    };

    model.addObserver(function()
    {
        if ( !list.opened )
        {
            counter.set(model.get('counter'));
        }
    });


    this.accept = function( requestKey, userId )
    {
        var item = list.getItem(requestKey);
        var c = {};

        if ( item.hasClass('peep_console_new_message') )
        {
            c["new"] = counter.get("new") - 1;
        }
        c["all"] = counter.get("all") - 1;
        counter.set(c);

        this.send('friends-accept', {id: userId});

        $('#friend_request_accept_'+userId).addClass( "peep_hidden");
        $('#friend_request_ignore_'+userId).addClass( "peep_hidden");

        return this;
    };

    this.ignore = function( requestKey, userId )
    {
        var item = list.getItem(requestKey);
        var c = {};

        this.send('friends-ignore', {id: userId});

        if ( item.hasClass('peep_console_new_message') )
        {
            c["new"] = counter.get("new") - 1;
        }
        c["all"] = counter.get("all") - 1;
        counter.set(c);

        list.removeItem(item);

        return this;
    };


    this.send = function( command, data )
    {
        var request = $.ajax({
            url: params.rsp,
            type: "POST",
            data: {
                "command": command,
                "data": JSON.stringify(data)
            },
            dataType: "json"
        });

        request.done(function( res )
        {
            if ( res && res.script )
            {
                PEEP.addScript(res.script);
            }
        });

        return this;
    };
}

PEEP.FriendRequest = null;