$(function () {
  $(".fittext").fitText();

  if ($("#newsletter_dlg").length) {
    $("#newsletter_dlg").modal("show");
    $("#newsletter_dlg")
      .find("form")
      .on("afterSubmit", function (e) {
        $("#newsletter_dlg").modal("hide");
      });
  }

  $("#share-link-button").on("click", function (event) {
    let element = $("#share-link")
    navigator.clipboard.writeText(element.val());
    $(element).attr("data-title", "Kopiert").tooltip("show");
  });

  $("#share-link-button").on("mouseleave", function (event) {
    $("#share-link").tooltip("dispose");
  });

  $("form.ajax-submit").on("submit", function (event) {
    event.preventDefault();
    let form = this;
    let formData = $(form).serialize();
    $.ajax({
      type: $(form).attr("method"),
      url: $(form).attr("action"),
      dataType: "JSON",
      data: formData,
    })
      .done(function (data) {
        $(form).trigger("afterSubmit");
      })
      .fail(function (data) {
        console.log(data);
      });
  });

  $(".editableLaps").on("click", function () {
    if ($(this).children().length == 0) {
      let cell = this;
      let row = $(cell).parent();
      let initialLaps = $(cell).text();
      $(cell).text("");

      let div1 = document.createElement("div");
      $(cell).append(div1);
      $(div1).attr("class", "input-group");

      let input = document.createElement("input");
      $(div1).append(input);
      $(input).attr("class", "form-control");
      $(input).attr("type", "number");
      $(input).attr("value", initialLaps);

      let div2 = document.createElement("div");
      $(div1).append(div2);
      $(div2).attr("class", "input-group-append");

      let button = document.createElement("button");
      $(div2).append(button);
      $(button).attr("class", "btn btn-outline-secondary");

      let icon = document.createElement("i");
      $(button).append(icon);
      $(icon).attr("class", "fa fa-save");

      $(div1).attr("style", "width: 120px;");

      $(input).trigger("focus");
      $(input).trigger("select");
      $(input).on("submit", function (e) {
        let runPartId = $(row).attr("row_id");
        let laps = $(input).val();
        let url = indexRunPartURL + "/" + runPartId;
        let CSRF_TOKEN = $('meta[name="csrf-token"]').attr("content");
        let result = $.ajax({
          url: url,
          type: "PATCH",
          dataType: "JSON",
          data: {
            _token: CSRF_TOKEN,
            laps: laps,
          },
        })
          .done(function (data) {
            $(cell).text(data["laps"]);
            $(row)
              .children("td[name=sum]")
              .text(data["sum"] + " €");
          })
          .fail(function (data) {
            $(input).addClass("is-invalid");
          });
      });
      $(input).on("exit", function (e) {
        $(cell).text(initialLaps);
      });

      $(input).on("keyup", function (e) {
        switch (e.keyCode) {
          case 13: // Enter
            $(input).trigger("submit");
            break;
          case 27: // Esc
            $(input).trigger("exit");
            break;
        }
      });

      $(document).on("mouseup", function (e) {
        if (!$(cell).is(e.target) && $(cell).has(e.target).length === 0) {
          $(input).trigger("exit");
        }
      });

      $(button).on("click", function (e) {
        $(input).trigger("submit");
      });
    }
  });

  $(".dataTable").DataTable();
});
