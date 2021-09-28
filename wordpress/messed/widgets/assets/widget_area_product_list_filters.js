jQuery(document).ready(function($) {
  // General => utilities functions
  var dictionary = null;

  function widgetAreaDictionary(widgetArea) {
    this.widgetArea = widgetArea;
    this.userChange = 0;
    this.userChange_calculate = function() {
      this.userChange = 0;
      var selector = {
        attributeFilter: ".woo_template_product_attribute_filter__list-item",
        priceFilterMin: '[name="min_price"]',
        priceFilterMax: '[name="max_price"]'
      };
      var price = priceFilter_get(widgetArea);
      var terms = widgetArea.querySelectorAll(selector.attributeFilter);
      if (price) {
        var min = price.querySelector(selector.priceFilterMin);
        var max = price.querySelector(selector.priceFilterMax);
        var minNow = Number(min.value);
        var maxNow = Number(max.value);
        var onPageLoad_min = Number(min.getAttribute("data-on-page-load"));
        var onPageLoad_max = Number(max.getAttribute("data-on-page-load"));
        minNow !== onPageLoad_min ? this.userChange_increment() : null;
        maxNow !== onPageLoad_max ? this.userChange_increment() : null;
      }
      if (terms) {
        terms = [...terms];
        terms.forEach(term => {
          var onPageLoad = term.getAttribute("data-on-page-load");
          onPageLoad = Boolean(Number(onPageLoad));
          var now = attributeFilter_term_getState(term);
          now !== onPageLoad ? this.userChange_increment() : null;
        });
      }
    };
    this.userChange_increment = function() {
      this.userChange++;
    };
  }
  function getQueryStringParamsArray(url) {
    var result = [];
    if (!url) return result;

    var pairs = url.split("?");
    if (pairs.length === 1) return result;
    //remove no parameters part
    pairs = pairs[1];
    // get other params if any
    pairs = pairs.split("&");

    // split every parameters into [key,value]
    pairs = pairs.map(p => {
      var temp = p.split("=");
      if (temp.length === 1) {
        return [p, ""];
      }
      return temp;
    });
    //return
    return pairs;
  }
  function createQueryStringByParamsArray(params) {
    //create query string
    var separator = "&";
    var queryString = "";
    var paramsLength = params.length;
    for (var t = 0; t < paramsLength; t++) {
      var param = params[t];
      var prefix = t === 0 ? "" : separator;
      queryString += prefix + param;
    }

    return queryString !== "" ? queryString : null;
  }

  //Woo Template - Widget Area - Product List Filters => utilities functions
  function productListFilters_widgetArea_get() {
    return document.querySelector(".product-list-filters-area");
  }
  //Woo Template - Widget - Multifilter Apply Reset => utilities functions
  function multifilterApplyReset_get(widgetArea) {
    return widgetArea.querySelector(".woo_template_multifilter_apply_reset");
  }
  function multifilterApplyReset_apply_updateView() {
    dictionary.userChange_calculate();
    var userChange = dictionary.userChange;
    var applyReset = multifilterApplyReset_get(dictionary.widgetArea);
    var apply = applyReset.querySelector("[data-apply-filters]");

    if (userChange > 0) {
      apply.removeAttribute("disabled");
    } else {
      apply.setAttribute("disabled", "");
    }
  }
  function multifilterApplyReset_reset_updateView() {
    var resetMustBeOn = false;

    var supportUrlSearchParams = "URLSearchParams" in window;
    supportUrlSearchParams = false;
    if (supportUrlSearchParams) {
      var searchParams = new URLSearchParams(window.location.search);
      resetMustBeOn = Boolean(
        Number(searchParams.get("reset_filters_must_be_on"))
      );
    } else {
      var searchParams = getQueryStringParamsArray(window.location.href);
      var temp = searchParams.filter(p => p[0] === "reset_filters_must_be_on");
      if (temp.length !== 0) {
        resetMustBeOn = Boolean(Number(temp[0][1]));
      }
    }

    if (resetMustBeOn) return;

    var applyReset = multifilterApplyReset_get(dictionary.widgetArea);
    var reset = applyReset.querySelector("[data-reset-filters]");
    reset.setAttribute("disabled", "");
    multifilterApplyReset_reset_updateView = function() {};
  }
  function multifilterApplyReset_updateView() {
    multifilterApplyReset_apply_updateView();
    multifilterApplyReset_reset_updateView();
  }
  //Woo Template - Widget - Product Attribute Filter => utilities functions
  function attributeFilter_allWidgets_get(widgetArea) {
    return widgetArea.querySelectorAll(
      ".woo_template_product_attribute_filter"
    );
  }
  function attributeFilter_term_getState(term) {
    var state = term.getAttribute("data-is-selected");
    if (!state) return false;
    state = Boolean(Number(state));
    return state;
  }
  function attributeFilter_term_setActive(term) {
    term.setAttribute("data-is-selected", "1");
    term.style.opacity = "1";
  }
  function attributeFilter_term_setInactive(term) {
    term.setAttribute("data-is-selected", "0");
    term.style.opacity = "0.5";
  }
  function attributeFilter_singleWidget_getConcatenateParamString(
    singleWidget
  ) {
    var separator = ",";
    var params = [];
    // get all terms
    var terms = singleWidget.querySelectorAll(
      ".woo_template_product_attribute_filter__list-item"
    );
    //convert to array
    terms = [...terms];
    // for every term of this widget...
    terms.forEach(term => {
      var state = attributeFilter_term_getState(term);
      // if this attribute is not selected continue with next one....
      if (state) {
        //get pairs of parameters
        var key = term.getAttribute("data-filter-name-key");
        var value = term.getAttribute("data-filter-name-value");
        params.push(key + "=" + value);
      }
    });

    if (params.length === 0) return null;

    // divide in pairs of key value
    params = params.map(param => param.split("="));

    // get key
    var key = params[0][0];

    //repopulate
    var concatenate = key + "=";
    var values = params.map(p => p[1]);
    values.forEach(v => {
      concatenate +=
        concatenate[concatenate.length - 1] === "=" ? v : separator + v;
    });

    var list = singleWidget.querySelector(
      ".woo_template_product_attribute_filter__list"
    );
    var queryType = list.getAttribute("data-query-type").toLowerCase();
    if (queryType !== "or") return concatenate;

    var keyPartial = terms[0].getAttribute("data-filter-name-key-partial");
    var orString = "query_type_" + keyPartial + "=or";

    return orString + "&" + concatenate;
  }
  function attributeFilter_allWidgets_getParamsArray(allWidgets) {
    var params = [];
    allWidgets = [...allWidgets];
    allWidgets.forEach(wid => {
      var temp = attributeFilter_singleWidget_getConcatenateParamString(wid);
      if (temp) {
        params.push(temp);
      }
    });
    return params.length !== 0 ? params : null;
  }
  function attributeFilter_allWidgets_onPageLoadSetState(widgetArea) {
    var widgets = widgetArea.querySelectorAll(
      ".woo_template_product_attribute_filter"
    );
    if (!widgets) return;
    widgets = [...widgets];
    widgets.forEach(wid => {
      var terms = wid.querySelectorAll(
        ".woo_template_product_attribute_filter__list-item"
      );
      terms = [...terms];
      terms.forEach(t => {
        var state = attributeFilter_term_getState(t);
        state
          ? attributeFilter_term_setActive(t)
          : attributeFilter_term_setInactive(t);
      });
    });
  }
  //Woocommerce - Widget - Price Filter => utilities functions
  function priceFilter_get(widgetArea) {
    return widgetArea.querySelector(".woocommerce.widget_price_filter");
  }
  function priceFilter_singleWidget_getConcatenateParamString(wPrice) {
    var inputMin = wPrice.querySelector('[name="min_price"]');
    var inputMax = wPrice.querySelector('[name="max_price"]');

    var minInitial = Number(inputMin.getAttribute("data-min"));
    var maxInitial = Number(inputMax.getAttribute("data-max"));

    var min = Number(inputMin.value);
    var max = Number(inputMax.value);

    var params = [];

    if (min !== minInitial) {
      params.push("min_price=" + min);
      // concatenate += "min_price=" + min;
    }
    if (max !== maxInitial) {
      params.push("max_price=" + max);
      // concatenate += "max_price=" + max;
    }

    return params.length !== 0 ? params : null;
  }
  // _______________________________________________________________________________
  // Script Main Function
  function init($) {
    // 0 - check if widget area is present
    var widgetArea = productListFilters_widgetArea_get();
    if (!widgetArea) return;
    // 0.5 - check if Multifilter Apply Reset widget is present
    var applyReset = multifilterApplyReset_get(widgetArea);
    if (!applyReset) return;

    // 1 - create a dictionary that store number of active filters during user experience
    dictionary = new widgetAreaDictionary(widgetArea);
    // 2 - set css of every product attribute filters widgets attribute based on active state
    attributeFilter_allWidgets_onPageLoadSetState(widgetArea);
    multifilterApplyReset_updateView();

    // 3 - add event => on click of Product attribute Filter widgets single attribute
    $("body").on(
      "click",
      ".product-list-filters-area .woo_template_product_attribute_filter__list-item, .product-list-filters-area button:not([data-url])",
      function(e) {
        if (this.tagName === "LI") {
          var state = attributeFilter_term_getState(this);
          var newState = !state;
          newState
            ? attributeFilter_term_setActive(this)
            : attributeFilter_term_setInactive(this);
        }
        multifilterApplyReset_updateView();
        e.preventDefault();
      }
    );
    // 3 - add event => on slide of price filter
    $("body").on("price_slider_slide", function() {
      multifilterApplyReset_updateView();
    });

    // 4 - add event => on click "apply filters"
    $("body").on(
      "click",
      ".product-list-filters-area button[data-apply-filters]",
      function(e) {
        var url = this.getAttribute("data-url");
        if (!url) return null;

        // get widget area wrapper
        var widgetArea = this.parentElement;
        while (!widgetArea.hasAttribute("data-plfwa")) {
          if (widgetArea.tagName === "BODY") return;
          widgetArea = widgetArea.parentElement;
        }

        // get all widgets of this area
        var wPrice = priceFilter_get(widgetArea);
        var wAttrFilters = attributeFilter_allWidgets_get(widgetArea);

        var params = [];
        var temp;

        // Woocommerce price filter widget
        if (wPrice) {
          temp = priceFilter_singleWidget_getConcatenateParamString(wPrice);
          if (temp) {
            temp.forEach(t => {
              params.push(t);
            });
            temp = null;
          }
        }
        // woo template product attribut Filters widgets
        if (wAttrFilters) {
          temp = attributeFilter_allWidgets_getParamsArray(wAttrFilters);
          if (temp) {
            temp.forEach(t => {
              params.push(t);
            });
            temp = null;
          }
        }

        // correct params
        if (params.length === 0) {
          return null;
        }

        // // divide in pairs of key value
        // params = params.map(param => param.split("="));

        // // create a new array contaning only the key
        // var paramsKey = params.map(param => param[0]);

        // // remove key duplicates
        // paramsKey = [...new Set(paramsKey)];

        // //repopulate using temp array based on query type
        // var tempParams = [];
        // paramsKey.forEach(key => {
        //   var item = key + "=";
        //   var valuesToAdd = params.filter(p => p[0] === key);
        //   valuesToAdd.forEach(v => {
        //     item += item[item.length - 1] === "=" ? v[1] : "," + v[1];
        //   });
        //   tempParams.push(item);
        // });

        // params = tempParams;
        // tempParams = null;
        // paramsKey = null;

        // add a "do-nothing-on-the-back-end" param to let know to "reset filter" button that we added something
        params.push("reset_filters_must_be_on=1");

        // generate query string
        var queryString = createQueryStringByParamsArray(params);

        if (!queryString) return;
        // add query string to url
        url += url[url.length - 1] === "/" ? "" : "/";
        url += "?" + queryString;

        // launch new page request
        window.open(url, "_top");
      }
    );

    // 5 - add event => on click "reset filters"
    $("body").on(
      "click",
      ".product-list-filters-area button[data-reset-filters]",
      function(e) {
        var url = this.getAttribute("data-url");
        if (!url) return null;

        // launch new page request
        window.open(url, "_top");
      }
    );
    // 6 - add event => on click inside widgetArea calculate if "reset button" must be enabled or not
  }

  init($);
});
