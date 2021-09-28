jQuery(document).ready(function ($) {
  /* =================================================== 
        Deserialize Query String to Js object
  =================================================== */
  const deparam = function (queryString) {
    const queryStringToJs = function QueryStringToHash(query) {
      var query_string = {};
      var vars = query.split("&");
      for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split("=");
        pair[0] = decodeURIComponent(pair[0]);
        pair[1] = decodeURIComponent(pair[1]);
        // If first entry with this name
        if (typeof query_string[pair[0]] === "undefined") {
          query_string[pair[0]] = pair[1];
          // If second entry with this name
        } else if (typeof query_string[pair[0]] === "string") {
          var arr = [query_string[pair[0]], pair[1]];
          query_string[pair[0]] = arr;
          // If third or later entry with this name
        } else {
          query_string[pair[0]].push(pair[1]);
        }
      }
      return query_string;
    };
    const convertBool = function (jsObj) {
      let newObj = {};
      for (const key in jsObj) {
        if (jsObj.hasOwnProperty(key)) {
          let value = jsObj[key];
          if (value === "false" || value === "true") {
            value = value === "true" ? true : false;
          }
          newObj[key] = value;
        }
      }
      return newObj;
    };
    const toJs = queryStringToJs(queryString);
    const boolRestored = convertBool(toJs);
    const finale = { ...boolRestored };
    return finale;
  };
  /* =================================================== 
        Listener 
  =================================================== */
  const AJAX_ERROR_HANDLER = function (event, jqXHR, settings, errorThrown) {
    debugger;
    const textStatus = jqXHR.statusText;
    const request = JSON.parse(JSON.stringify(settings));
    request.dataJS = deparam(request.data);
    WT_AJAX_CLIENT_ERROR_HANDLER(jqXHR, textStatus, errorThrown, request);
  };
  /* =================================================== 
        Register Global Handler for $.AjaxError 
  =================================================== */
  $(document).ajaxError(AJAX_ERROR_HANDLER);
});

const WT_AJAX_CLIENT_CONTROLLER = (function () {
  const $ = jQuery;

  /* =================================================== 
        Ajax Runner
  =================================================== */

  return function (request) {
    const {
      method,
      dataToStringify,
      endpoint,
      onSuccess,
      onError,
      timeout = 5000,
    } = request;

    $.ajax({
      type: String(method).toUpperCase().trim(),
      url: endpoint,
      timeout: timeout,
      data: dataToStringify,
      dataType: "json",
      success: function (response, textStatus, jqXHR) {
        onSuccess(response);
      },
      error: function (jqXHR, textStatus, errorThrown) {
        onError();
      },
    });
  };
})();
