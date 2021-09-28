jQuery(document).ready(function ($) {
  const selectors = {
    trigger: ".plaese-register-trigger",
    target: ".plaese-register-target",
    closer: ".plaese-register-closer",
  };

  const target = document.querySelector(selectors.target);

  // early abort
  if (!target) {
    return null;
  }

  // target panel actions
  const showedClass = "is-showed";
  const TargetController = {
    Show: () => target.classList.add(showedClass),
    Hide: () => target.classList.remove(showedClass),
  };
  $(document.body).on("please_register_show", TargetController.Show);
  $(document.body).on("please_register_hide", TargetController.Hide);

  // all triggers in the page ...
  j.on(document.body, "click", selectors.trigger, function () {
    $(document.body).trigger("please_register_show");
  });
  // all closers in the page ...
  j.on(document.body, "click", selectors.closer, function () {
    $(document.body).trigger("please_register_hide");
  });
});
