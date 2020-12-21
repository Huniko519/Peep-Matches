var CI_Facebook = function(libUrl, userId, urlForInvite)
{
	var self = this;

        this.init = function(params)
        {
            $('body').prepend('<div id="fb-root"></div>');

            window.fbAsyncInit = function() {
                FB.init(params);
            };

            (function() {
                var e = document.createElement('script');
                e.src = libUrl;
                e.async = true;
                document.getElementById('fb-root').appendChild(e);
            }());
        }

        this.requireLogin = function(func)
        {
            FB.getLoginStatus(function(response)
            {
                if (response.authResponse)
                {
                    func(response);
                }
                else
                {
                    FB.login(function(r)
                    {
                        if (r.authResponse)
                        {
                            func(r);
                        }
                    });
                }
            });
        };

	this.request = function()
        {
            this.requireLogin(function(r){
                FB.ui({
                    method: 'send',
                    link: urlForInvite
                
                }, function(res){
                    if ( res.to && res.to.length )
                    {
                        PEEP.info(PEEP.getLanguageText('contactimporter', 'facebook_after_invite_feedback', {
                            count: res.to.length
                        }));
                    }
		});
            });
	};
};