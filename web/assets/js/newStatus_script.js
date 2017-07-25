/* LIB */
var getLocation = function(href) {
    var l = document.createElement("a");
    l.href = href;
    return l;
};


var connected=false;

if(!$("#SESSION_username").text()){
    $("form[name='appbundle_status'] *").attr("disabled", "disabled").attr("placeholder","");
}


$("#MastodonAuth").submit(function (evt) {
    console.log("Mastodon Authentification...");

    $("#MastodonAuth [type=\"submit\"]").text("Loading...");

    evt.preventDefault();

    // Check instance


    localStorage.setItem("mastodon_instance", $("#MastodonAuth_instance").val());

    var M = new MastodonAPI({
        instance: $("#MastodonAuth_instance").val(),
        api_user_token: ""
    });

    $.ajax({
            url: "/auth/"+getLocation($("#MastodonAuth_instance").val()).hostname
    }).done(function( data ) {
        app=JSON.parse(data);
        console.log(app);
        if(app.response=="ok"){
            window.location.href = M.generateAuthLink(app.app_id,
                app.redirect_url,
                "code", // oauth method
                ["read", "write"]
            );
        }else{
            $("#MastodonAuth [type=\"submit\"]").text("Error");
        }
    }).fail(function (data) {
        $("#MastodonAuth [type=\"submit\"]").text("Error");
        $("#MastodonAuth [type=\"submit\"]+.help-block").text("The url is not valid. Try again.")
    });


});//form submit



$("[name='appbundle_status']").submit(function (evt) {
    if($("#appbundle_status_content").val().length>500){
        evt.preventDefault();
        $(".alert-danger").remove();
        $(".page-header").after('<div class="alert alert-danger" role="alert"><strong>Error:</strong> Toot content is &gt; 500 char</div>');
    }
});