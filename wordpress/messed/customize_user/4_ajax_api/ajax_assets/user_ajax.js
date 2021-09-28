const USER_AJAX = (function () {
  /* =================================================== 
        EARLY ABORT
  =================================================== */
  // we have endpoints to AJAX API ?
  if (!user_ajax_params) return null;
  // we have AJAX CLIENT RUNNER ?
  if (!WT_AJAX_CLIENT_CONTROLLER) return null;


  /* =================================================== 
        API COMMUNICATION SETUP
  =================================================== */
  const endpoints = {
    _prefix: user_ajax_params.wc_ajax_url.toString(),
    maybe_exists: user_ajax_params.endpoints.maybe_exists,
    login: user_ajax_params.endpoints.login,
    create_user: user_ajax_params.endpoints.create_user,
  };

  /* =================================================== 
        STEPS
  =================================================== */
  const ABSTRACT_AJAX_CALL = function (key, method) {
    const endpoint = endpoints[key];
    const {_prefix} = endpoints;
    const url = _prefix.replace("%%endpoint%%", endpoint);
    
    return function ({ dataToStringify, onSuccess, onError }) {
      const { ajaxAdditionalParams = {} } = this;
      const req = {
        method: method,
        dataToStringify: { ...dataToStringify, [key]: true, ...ajaxAdditionalParams },
        endpoint: url,
        onSuccess: onSuccess,
        onError: onError,
      };
      WT_AJAX_CLIENT_CONTROLLER(req);
    };
  };


  return {
    maybe_exists: ABSTRACT_AJAX_CALL("maybe_exists", "post"),
    login: ABSTRACT_AJAX_CALL("login", "post"),
    create_user: ABSTRACT_AJAX_CALL("create_user", "post"),
  };

})();
