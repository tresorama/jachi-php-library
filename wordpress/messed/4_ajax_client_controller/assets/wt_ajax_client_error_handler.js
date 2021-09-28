const WT_AJAX_CLIENT_ERROR_HANDLER = (function () {
  /* =================================================== 
        EARLY ABORT
  =================================================== */
  if (!jQuery) return null;
  if (!WT_AJAX_SAVE_CLIENT_ERROR_PARAMS) return null;

  /* =================================================== 
        Import
  =================================================== */
  const $ = jQuery;

  /* =================================================== 
        Get Language
  =================================================== */
  const locale = (function () {
    const lang = document.querySelector("html").getAttribute("lang");
    switch (lang) {
      case "it-IT":
        return "IT";
      default:
        return "EN";
    }
  })();

  /* =================================================== 
      ERRORS DICTIONARY 
  =================================================== */

  const READYSTATE_ERRORS = {
    0: {
      code: 0,
      name: "UNSENT",
      desc:
        "Il Client è stato creato, ma il metodo open() della XHR non è stato ancora invocato.",
    },
    1: {
      code: 1,
      name: "OPENED",
      desc:
        "Il Client è stato creato, ma il metodo open() della XHR non è stato ancora invocato.",
    },
    2: {
      code: 2,
      name: "HEADERS_RECEIVED",
      desc:
        "Il metodo send() della XHR è stato invocato, e sono già disponibili lo status della risposta HTTP ed il suo header.",
    },
    3: {
      code: 3,
      name: "LOADING",
      desc: "Sta avvenendo il download dei dati; responseText contiene dati parziali.",
    },
    4: {
      code: 4,
      name: "DONE",
      desc: "Risposta ricevuta, nessun problema di comuniczione con il server.",
    },
  };
  const HTTP_ERROR_CODES = {
    100: {
      name: "Continue",
      desc:
        "This interim response indicates that everything so far is OK and that the client should continue the request, or ignore the response if the request is already finished.",
    },
    101: {
      name: "Switching Protocol",
      desc:
        "This code is sent in response to an Upgrade request header from the client, and indicates the protocol the server is switching to.",
    },
    102: {
      name: "Processing",
      desc:
        "This code indicates that the server has received and is processing the request, but no response is available yet.",
    },
    103: {
      name: "Early Hints",
      desc:
        "This status code is primarily intended to be used with the Link header, letting the user agent start preloading resources while the server prepares a response.",
    },
    200: {
      name: "OK",
      desc: "ALL RIGHT",
    },
    201: {
      name: "Created",
      desc:
        "The request has succeeded and a new resource has been created as a result. This is typically the response sent after POST requests, or some PUT requests.",
    },
    202: {
      name: "Accepted",
      desc:
        "The request has been received but not yet acted upon. It is noncommittal, since there is no way in HTTP to later send an asynchronous response indicating the outcome of the request. It is intended for cases where another process or server handles the request, or for batch processing.",
    },
    203: {
      name: "Non-Authoritative Information",
      desc:
        "This response code means the returned meta-information is not exactly the same as is available from the origin server, but is collected from a local or a third-party copy. This is mostly used for mirrors or backups of another resource. Except for that specific case, the '200 OK' response is preferred to this status.",
    },
    204: {
      name: "No Content",
      desc:
        "There is no content to send for this request, but the headers may be useful. The user-agent may update its cached headers for this resource with the new ones.",
    },
    205: {
      name: "Reset Content",
      desc: "Tells the user-agent to reset the document which sent this request.",
    },
    206: {
      name: "Partial Content",
      desc:
        "This response code is used when the Range header is sent from the client to request only part of a resource.",
    },
    207: {
      name: "Multi-Status",
      desc:
        "Conveys information about multiple resources, for situations where multiple status codes might be appropriate.",
    },
    208: {
      name: "Already Reported",
      desc:
        "Used inside a <dav:propstat> response element to avoid repeatedly enumerating the internal members of multiple bindings to the same collection.",
    },
    226: {
      name: "IM Used",
      desc:
        "The server has fulfilled a GET request for the resource, and the response is a representation of the result of one or more instance-manipulations applied to the current instance.",
    },
    300: {
      name: "Multiple Choice",
      desc:
        "The request has more than one possible response. The user-agent or user should choose one of them. (There is no standardized way of choosing one of the responses, but HTML links to the possibilities are recommended so the user can pick.)",
    },
    301: {
      name: "Moved Permanently",
      desc:
        "The URL of the requested resource has been changed permanently. The new URL is given in the response.",
    },
    302: {
      name: "Found",
      desc:
        "This response code means that the URI of requested resource has been changed temporarily. Further changes in the URI might be made in the future. Therefore, this same URI should be used by the client in future requests.",
    },
    303: {
      name: "See Other",
      desc:
        "The server sent this response to direct the client to get the requested resource at another URI with a GET request.",
    },
    304: {
      name: "Not Modified",
      desc:
        "This is used for caching purposes. It tells the client that the response has not been modified, so the client can continue to use the same cached version of the response.",
    },
    305: {
      name: "Use Proxy",
      desc:
        "Defined in a previous version of the HTTP specification to indicate that a requested response must be accessed by a proxy. It has been deprecated due to security concerns regarding in-band configuration of a proxy.",
    },
    306: {
      name: "unused",
      desc:
        "This response code is no longer used; it is just reserved. It was used in a previous version of the HTTP/1.1 specification.",
    },
    307: {
      name: "Temporary Redirect",
      desc:
        "The server sends this response to direct the client to get the requested resource at another URI with same method that was used in the prior request. This has the same semantics as the 302 Found HTTP response code, with the exception that the user agent must not change the HTTP method used: If a POST was used in the first request, a POST must be used in the second request.",
    },
    308: {
      name: "Permanent Redirect",
      desc:
        "This means that the resource is now permanently located at another URI, specified by the Location: HTTP Response header. This has the same semantics as the 301 Moved Permanently HTTP response code, with the exception that the user agent must not change the HTTP method used: If a POST was used in the first request, a POST must be used in the second request.",
    },
    400: {
      name: "Bad Request",
      desc: "The server could not understand the request due to invalid syntax.",
    },
    401: {
      name: "Unauthorized",
      desc:
        "Although the HTTP standard specifies 'unauthorized', semantically this response means 'unauthenticated'. That is, the client must authenticate itself to get the requested response.",
    },
    402: {
      name: "Payment Required",
      desc:
        "This response code is reserved for future use. The initial aim for creating this code was using it for digital payment systems, however this status code is used very rarely and no standard convention exists.",
    },
    403: {
      name: "Forbidden",
      desc:
        "The client does not have access rights to the content; that is, it is unauthorized, so the server is refusing to give the requested resource. Unlike 401, the client's identity is known to the server.",
    },
    404: {
      name: "Not Found",
      desc:
        "The server can not find the requested resource. In the browser, this means the URL is not recognized. In an API, this can also mean that the endpoint is valid but the resource itself does not exist. Servers may also send this response instead of 403 to hide the existence of a resource from an unauthorized client. This response code is probably the most famous one due to its frequent occurrence on the web.",
    },
    405: {
      name: "Method Not Allowed",
      desc:
        "The request method is known by the server but has been disabled and cannot be used. For example, an API may forbid DELETE-ing a resource. The two mandatory methods, GET and HEAD, must never be disabled and should not return this error code.",
    },
    406: {
      name: "Not Acceptable",
      desc:
        "This response is sent when the web server, after performing server-driven content negotiation, doesn't find any content that conforms to the criteria given by the user agent.",
    },
    407: {
      name: "Proxy Authentication Required",
      desc: "This is similar to 401 but authentication is needed to be done by a proxy.",
    },
    408: {
      name: "Request Timeout",
      desc:
        "This response is sent on an idle connection by some servers, even without any previous request by the client. It means that the server would like to shut down this unused connection. This response is used much more since some browsers, like Chrome, Firefox 27+, or IE9, use HTTP pre-connection mechanisms to speed up surfing. Also note that some servers merely shut down the connection without sending this message.",
    },
    409: {
      name: "Conflict",
      desc:
        "This response is sent when a request conflicts with the current state of the server.",
    },
    410: {
      name: "Gone",
      desc:
        "This response is sent when the requested content has been permanently deleted from server, with no forwarding address. Clients are expected to remove their caches and links to the resource. The HTTP specification intends this status code to be used for 'limited-time, promotional services'. APIs should not feel compelled to indicate resources that have been deleted with this status code.",
    },
    411: {
      name: "Length Required",
      desc:
        "Server rejected the request because the Content-Length header field is not defined and the server requires it.",
    },
    412: {
      name: "Precondition Failed",
      desc:
        "The client has indicated preconditions in its headers which the server does not meet.",
    },
    413: {
      name: "Payload Too Large",
      desc:
        "Request entity is larger than limits defined by server; the server might close the connection or return an Retry-After header field.",
    },
    414: {
      name: "URI Too Long",
      desc:
        "The URI requested by the client is longer than the server is willing to interpret.",
    },
    415: {
      name: "Unsupported Media Type",
      desc:
        "The media format of the requested data is not supported by the server, so the server is rejecting the request.",
    },
    416: {
      name: "Range Not Satisfiable",
      desc:
        "The range specified by the Range header field in the request can't be fulfilled; it's possible that the range is outside the size of the target URI's data.",
    },
    417: {
      name: "Expectation Failed",
      desc:
        "This response code means the expectation indicated by the Expect request header field can't be met by the server.",
    },
    418: {
      name: "I'm a teapot",
      desc: "The server refuses the attempt to brew coffee with a teapot.",
    },
    421: {
      name: "Misdirected Request",
      desc:
        "The request was directed at a server that is not able to produce a response. This can be sent by a server that is not configured to produce responses for the combination of scheme and authority that are included in the request URI.",
    },
    422: {
      name: "Unprocessable Entity",
      desc:
        "The request was well-formed but was unable to be followed due to semantic errors.",
    },
    423: {
      name: "Locked",
      desc: "The resource that is being accessed is locked.",
    },
    424: {
      name: "Failed Dependency",
      desc: "The request failed due to failure of a previous request.",
    },
    425: {
      name: "Too Early",
      desc:
        "Indicates that the server is unwilling to risk processing a request that might be replayed.",
    },
    426: {
      name: "Upgrade Required",
      desc:
        "The server refuses to perform the request using the current protocol but might be willing to do so after the client upgrades to a different protocol. The server sends an Upgrade header in a 426 response to indicate the required protocol(s).",
    },
    428: {
      name: "Precondition Required",
      desc:
        "The origin server requires the request to be conditional. This response is intended to prevent the 'lost update' problem, where a client GETs a resource's state, modifies it, and PUTs it back to the server, when meanwhile a third party has modified the state on the server, leading to a conflict.",
    },
    429: {
      name: "Too Many Requests",
      desc:
        "The user has sent too many requests in a given amount of time ('rate limiting').",
    },
    431: {
      name: "Request Header Fields Too Large",
      desc:
        "The server is unwilling to process the request because its header fields are too large. The request may be resubmitted after reducing the size of the request header fields.",
    },
    451: {
      name: "Unavailable For Legal Reasons",
      desc:
        "The user-agent requested a resource that cannot legally be provided, such as a web page censored by a government.",
    },
    500: {
      name: "Internal Server Error",
      desc: "The server has encountered a situation it doesn't know how to handle.",
    },
    501: {
      name: "Not Implemented",
      desc:
        "The request method is not supported by the server and cannot be handled. The only methods that servers are required to support (and therefore that must not return this code) are GET and HEAD.",
    },
    502: {
      name: "Bad Gateway",
      desc:
        "This error response means that the server, while working as a gateway to get a response needed to handle the request, got an invalid response.",
    },
    503: {
      name: "Service Unavailable",
      desc:
        "The server is not ready to handle the request. Common causes are a server that is down for maintenance or that is overloaded. Note that together with this response, a user-friendly page explaining the problem should be sent. This responses should be used for temporary conditions and the Retry-After: HTTP header should, if possible, contain the estimated time before the recovery of the service. The webmaster must also take care about the caching-related headers that are sent along with this response, as these temporary condition responses should usually not be cached.",
    },
    504: {
      name: "Gateway Timeout",
      desc:
        "This error response is given when the server is acting as a gateway and cannot get a response in time.",
    },
    505: {
      name: "HTTP Version Not Supported",
      desc: "The HTTP version used in the request is not supported by the server.",
    },
    506: {
      name: "Variant Also Negotiates",
      desc:
        "The server has an internal configuration error: the chosen variant resource is configured to engage in transparent content negotiation itself, and is therefore not a proper end point in the negotiation process.",
    },
    507: {
      name: "Insufficient Storage",
      desc:
        "The method could not be performed on the resource because the server is unable to store the representation needed to successfully complete the request.",
    },
    508: {
      name: "Loop Detected",
      desc: "The server detected an infinite loop while processing the request.",
    },
    510: {
      name: "Not Extended",
      desc: "Further extensions to the request are required for the server to fulfil it.",
    },
    511: {
      name: "Network Authentication Required",
      desc:
        "The 511 status code indicates that the client needs to authenticate to gain network access.",
    },
  };
  const BROWSER_NICE_ERROR_MESSAGES = {
    firstError: {
      IT: "Si è verificato un problema, riprova!",
      EN: "Error occured, please retry",
    },
    againError: {
      IT:
        "Si è riverificato lo stesso problema, probabilmente non sei connesso ad internet, ricarica la pagina e riprova!",
      EN:
        "The same problem has occurred again, you are probably not connected to the internet, reload the page and try again!",
    },
  };

  /* =================================================== 
        CREATE ERROR DETAILS
  =================================================== */

  const getErrorArea = function (code) {
    let area = null;
    switch (true) {
      case code >= 100 && code <= 199:
        area = "Informational responses";
        break;
      case code >= 200 && code <= 299:
        area = "Successful responses";
        break;
      case code >= 300 && code <= 399:
        area = "Redirects";
        break;
      case code >= 400 && code <= 499:
        area = "Client errors";
        break;
      case code >= 500 && code <= 599:
        area = "Server errors";
        break;
      default:
        area = "CUSTOM_ERROR";
        break;
    }
    return area;
  };
  const READY_STATE = function (readyState) {
    return READYSTATE_ERRORS[readyState] || {};
  };
  const ERROR_DETAILS = function (jqXHR, textStatus) {
    if (textStatus === "timeout") {
      return {
        code: -1,
        area: "Timeout",
        name: "Timeout",
        desc: "Time limit reached without response.",
      };
    }

    const code = jqXHR.status;

    return {
      code: code,
      area: getErrorArea(code),
      name: HTTP_ERROR_CODES[code] ? HTTP_ERROR_CODES[code].name : "APP_CUSTOM_ERROR",
      desc: HTTP_ERROR_CODES[code]
        ? HTTP_ERROR_CODES[code].desc
        : "No description provided.",
    };
  };
  const ERROR_NICE_MESSAGE = function () {
    const firstError = BROWSER_NICE_ERROR_MESSAGES.firstError[locale];
    const DOM = DOM_Storage();
    const alreadyHappened = DOM.getMessages().find((m) => m === firstError);
    if (alreadyHappened) {
      const againError = BROWSER_NICE_ERROR_MESSAGES.againError[locale];
      return againError;
    }
    return firstError;
  };

  /* =================================================== 
        Html Storage DOM Element
  =================================================== */
  const DOM_Storage = function () {
    return {
      ids: {
        id_wrapper: "client-error-storage",
        id_store_error: "store-error",
        id_store_message: "store-message",
      },
      elements: {},
      insertDomElement: function (id, parent) {
        const selector = "#" + id;
        let element = parent.querySelector(selector);
        if (!element) {
          element = document.createElement("div");
          element.id = id;
          parent.appendChild(element);
        }
        return element;
      },
      init: function () {
        const { id_wrapper, id_store_error, id_store_message } = this.ids;
        const wrapper = this.insertDomElement(id_wrapper, document.body);
        const storeErr = this.insertDomElement(id_store_error, wrapper);
        const storeMsg = this.insertDomElement(id_store_message, wrapper);
        this.elements = {
          wrapper: wrapper,
          storeErr: storeErr,
          storeMsg: storeMsg,
        };
      },
      addError: function (error) {
        this.init();
        const { storeErr } = this.elements;
        if (!storeErr._STORE) {
          storeErr._STORE = [];
        }
        storeErr._STORE.push(error);
      },
      addMessage: function (message) {
        this.init();
        const { storeMsg } = this.elements;
        if (!storeMsg._STORE) {
          storeMsg._STORE = [];
        }
        storeMsg._STORE.push(message);
      },
      clearErrors: function () {
        this.init();
        const { storeErr } = this.elements;
        storeErr._STORE = [];
      },
      clearMessages: function () {
        this.init();
        const { storeMsg } = this.elements;
        storeMsg._STORE = [];
      },
      removeError: function (error) {
        this.init();
        const { storeErr } = this.elements;
        storeErr._STORE = storeErr._STORE.filter((item) => item !== error);
      },
      removeMessage: function (message) {
        this.init();
        const { storeMsg } = this.elements;
        storeMsg._STORE = storeMsg._STORE.filter((item) => item !== error);
      },
      getErrors: function () {
        this.init();
        const { storeErr } = this.elements;
        return storeErr._STORE ? [...storeErr._STORE] : [];
      },
      getMessages: function () {
        this.init();
        const { storeMsg } = this.elements;
        return storeMsg._STORE ? [...storeMsg._STORE] : [];
      },
    };
  };
  const STORE_IN_DOM = function (message) {
    const DOM = DOM_Storage();
    DOM.addMessage(message);
  };

  /* =================================================== 
        Log in Database errors
  =================================================== */
  const ERROR_TO_DB_QUEUE = {
    queue: [],
    yetSaved: [],
    TAG_AS_NOT_SAVED: function (id) {
      this.queue = this.queue.map((item) => {
        if (item.id === id) {
          // copy all props but sending
          const { sending, ...notSaved } = item;
          return notSaved;
        }
        return item;
      });
    },
    TAG_AS_SAVED: function (id) {
      const found = this.queue.find((item) => item.id === id);
      if (found) {
        // copy all props but sending
        const { sending, ...savedItem } = found;
        //save in yet saved array
        this.yetSaved.push(savedItem);
      }
      //remove from queue
      this.queue = this.queue.filter((item) => item.id !== id);
    },
    ADD: function (error, onSuccess, onError) {
      // build item to add to queue
      const id = Date.now();
      const { url, action } = WT_AJAX_SAVE_CLIENT_ERROR_PARAMS;
      const request = {
        type: "POST",
        url: url,
        timeout: 3000,
        data: {
          action: action,
          client_error: error,
        },
        dataType: "json",
        success: function (response, textStatus, jqXHR) {
          //all ok ???
          const success = response ? response.success === true : false;
          if (success) {
            //remove from queue
            this.TAG_AS_SAVED(id);
            //call additional
            onSuccess(response, textStatus, jqXHR);
          }
          if (!success) {
            // tell queue that this error was not saved to db
            this.TAG_AS_NOT_SAVED(id);
          }
        }.bind(this),
        error: function () {
          this.TAG_AS_NOT_SAVED(id);
          onError();
        }.bind(this),
        global: false,
      };
      // add to queue
      this.queue.push({ id: id, request: request });
    },
    MAYBE_SEND_ERROR_TO_DB: function () {
      const toSend = this.queue.filter((item) => item.sending !== true);
      if (toSend.length > 0) {
        toSend.forEach((item) => {
          const { id, request } = item;
          item.sending = true;
          $.ajax(request);
        });
      }
    },
  };
  window.addEventListener("beforeunload", function (event) {
    ERROR_TO_DB_QUEUE.MAYBE_SEND_ERROR_TO_DB();
  });

  const SAVE_TO_DB = function ({ data, onSuccess = () => {}, onError = () => {} }) {
    ERROR_TO_DB_QUEUE.ADD(data, onSuccess, onError);
    ERROR_TO_DB_QUEUE.MAYBE_SEND_ERROR_TO_DB();
  };

  /* =================================================== 
        BROWSER RENDER FUNCTIONS
  =================================================== */
  const ALERT = (message) => alert(message);
  const LOG = (e) => console.table(e);
  const RENDER_IN_BROWSER = function (data) {
    const { niceMessage, errorDetails } = data;
    LOG(errorDetails);
    ALERT(niceMessage);
  };

  /* =================================================== 
        PROCESS THIS ERROR
  =================================================== */
  return function (jqXHR, textStatus, errorThrown, request) {
    //build nice message
    const niceMessage = ERROR_NICE_MESSAGE();
    //build full error data
    const data = {
      jqXHR: jqXHR,
      textStatus: textStatus,
      errorThrown: errorThrown,
      errorDetails: {
        readyState: READY_STATE(jqXHR.readyState),
        httpError: ERROR_DETAILS(jqXHR, textStatus),
      },
      niceMessage: niceMessage,
      request: request,
      page: WT_AJAX_SAVE_CLIENT_ERROR_PARAMS.page,
    };
    // store message in dom
    STORE_IN_DOM(niceMessage);
    //render error in browser
    RENDER_IN_BROWSER(data);
    // send error to db
    SAVE_TO_DB({
      data: JSON.parse(JSON.stringify(data)),
      onSuccess: function (response, textStatus, jqXHR) {},
      onError: function () {},
    });
  };
})();
