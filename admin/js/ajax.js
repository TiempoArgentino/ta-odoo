(function ($) {
  //sync clientes
  $(document).ready(function () {
    $("#syn-contacts-loading").hide();
    $("#btn-sync-contact").on("click", function () {
      if (confirm(odoo_vars.msg_confirm)) {
        var checked = []
        $("input[name='role[]']:checked").each(function ()
        {
          checked.push($(this).val());
        });
        $.ajax({
          type: "post",
          url: odoo_vars.ajaxurl,
          data: "action=" + odoo_vars.action + "&nonce=" + odoo_vars.nonce + "&roles=" + checked,
          beforeSend: function () {
            $("#btn-sync-contact").hide();
            $("#syn-contacts-loading").show("fast");
          },
        })
          .done(function (response) {
           // console.log(response);
            $("#btn-sync-contact").show("fast");
            $("#syn-contacts-loading")
              .html(odoo_vars.msg_success)
              .css("background-image", "none");
          })
          .fail(function (response, xhr, status) {
            console.error('xhr: ' + xhr);
            console.error('status: ' + status);
            console.error('error: ' + response);
            $("#btn-sync-contact").show("fast");
            $("#syn-contacts-loading")
              .html(odoo_vars.msg_error)
              .css("background-image", "none");
          });
      } else {
        alert(odoo_vars.msg_odoo);
      }
    });
  });
})(jQuery);
