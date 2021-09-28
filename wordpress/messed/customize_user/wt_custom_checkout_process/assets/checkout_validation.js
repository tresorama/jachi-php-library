const CHECKOUT_VALIDATION = (function () {
  /* =================================================== 
        EARLY ABORT
  =================================================== */
  // we have joi validation script ?
  if (!joi) return null;
  if (!ABSTRACT_VALIDATION) return null;

  /* =================================================== 
        JOI
  =================================================== */
  const Joi = joi;

  /* =================================================== 
        BUILD SCHEMAS
  =================================================== */

  const schemas = {
    shipping_info: Joi.object({
      // FIRST NAME
      first_name: Joi.string().required(),
      // LAST NAME
      last_name: Joi.string().required(),
      // COUNTRY
      country: Joi.string().required(),
      // STATE
      state: Joi.string(),
      // CITY
      city: Joi.string().required(),
      // POSTCODE
      postcode: Joi.string().required(),
      // ADDRESS
      address: Joi.string().required(),
      // CO
      co: Joi.string(),
      // PHONE
      tel: Joi.string().required(),
    }),
    billing_info: Joi.object({
      // FIRST NAME
      billing_first_name: Joi.string().required(),
      // LAST NAME
      billing_last_name: Joi.string().required(),
      // LAST NAME
      billing_company: Joi.string(),
      // COUNTRY
      billing_country: Joi.string().required(),
      // STATE
      billing_state: Joi.string(),
      // CITY
      billing_city: Joi.string().required(),
      // POSTCODE
      billing_postcode: Joi.string().required(),
      // ADDRESS
      billing_address_1: Joi.string().required(),
      // PHONE
      billing_phone: Joi.string().required(),
      // PHONE
      billing_email: Joi.string()
        .email({ tlds: { allow: false } })
        .required(),
    }),
  };

  /* =================================================== 
  BUILD ERROR MESSAGE MAP - used for compose error messages
  =================================================== */

  const ERROR_MESSAGE_MAP = {
    label: {
      first_name: { it: "Nome", eng: "First Name" },
      last_name: { it: "Cognome", eng: "Last Name" },
      country: { it: "Nazione", eng: "Country" },
      state: { it: "Provincia", eng: "State" },
      city: { it: "Città", eng: "City" },
      postcode: { it: "CAP", eng: "Postcode" },
      address: { it: "Indirizzo", eng: "Address" },
      co: { it: "Interno", eng: "C/O" },
      tel: { it: "Telefono", eng: "Phone" },
    },
    message: {
      required: { it: "{{nome}} è un campo richiesto.", en: "{{nome}} it's required." },
      email: {
        it: "{{value}} non è una mail valida.",
        en: "{{value}} is not a valid email.",
      },
      empty: { it: "{{nome}} non può essere vuoto.", en: "{{nome}} can not be empty." },
      only: {
        it: "{{nome}} e {{ref}} devono essere uguali.",
        en: "{{nome}} and {{ref}} must match.",
      },
      min: {
        it: "{{nome}} deve avere almeno {{min}} caratteri.",
        en: "{{nome}} length must be at least {{min}} characters.",
      },
      max: {
        it: "{{nome}} deve avere massimo {{max}} caratteri.",
        en: "{{nome}} length must be max {{max}} characters.",
      },
    },
  };

  /* =================================================== 
        BUILD FUNCTIONS TO BE USED
  =================================================== */

  return {
    shipping_info: ABSTRACT_VALIDATION(schemas["shipping_info"], ERROR_MESSAGE_MAP),
    billing_info: ABSTRACT_VALIDATION(schemas["billing_info"], ERROR_MESSAGE_MAP),
  };
})();
