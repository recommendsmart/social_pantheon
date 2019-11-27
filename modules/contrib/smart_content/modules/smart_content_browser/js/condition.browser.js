(function ($) {

  Drupal.smart_content.SmartContentManager.plugin = Drupal.smart_content.SmartContentManager.plugin || {};
  Drupal.smart_content.SmartContentManager.plugin.Field = Drupal.smart_content.SmartContentManager.plugin.Field || {};
  Drupal.smart_content.SmartContentManager.plugin.Field.browserSmartCondition = {
    init: function (Field) {
      if (Field.pluginId == 'browser:language') {
        Field.claim();
        var language = window.navigator.userLanguage || window.navigator.language;
        Field.complete(language);

      }
      else if (Field.pluginId == 'browser:mobile') {
        Field.claim();
        var mobile = Boolean((typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1));
        Field.complete(mobile);

      }
      else if (Field.pluginId == 'browser:platform_os') {
        Field.claim();
        var platform = window.navigator.platform;
        var ua = window.navigator.userAgent;
        var os = '';
        if (platform === 'MacIntel' || platform === 'MacPPC') {
          os = 'macosx';
        }
        else if (platform === 'CrOS') {
          os = 'chromeos';
        }
        else if (platform === 'Win32' || platform === 'Win64') {
          os = 'windows';
        }
        else if (/Windows/i.test(ua)) {
          os = 'windows';
        }
        else if (/Android/i.test(ua) || /Linux armv7l/i.test(platform)) {
          os = 'android';
        }
        else if (/Linux/i.test(platform)) {
          os = 'linux';
        }
        // IE11 includes 'iPhone' in its userAgent, so we need to check for it
        else if (/iPad|iPhone|iPod/i.test(ua) && !window.MSStream) {
          os = 'ios'
        }
        else if (/Nintendo/i.test(platform)) {
          os = 'nintendo';
        }
        else if (/PlayStation/i.test(platform)) {
          os = 'playstation';
        }
        Field.complete(os);

      }
      else if (Field.pluginId == 'browser:cookie_enabled') {
        Field.claim();
        Field.complete(navigator.cookieEnabled);
      }
      else if (Field.pluginId == 'browser:cookie') {
        Field.claim();
        cookie_value = $.cookie(Field.settings.key);
        Field.complete(cookie_value);
      }
      else if (Field.pluginId == 'browser:localstorage') {
        Field.claim();
        Field.complete(localStorage[Field.settings.key]);
      }
      else if (Field.pluginId == 'browser:width') {
        Field.claim();
        Field.complete(Math.max(
          document.body.scrollWidth,
          document.documentElement.scrollWidth,
          document.body.offsetWidth,
          document.documentElement.offsetWidth,
          document.documentElement.clientWidth
        ));
      }
      else if (Field.pluginId == 'browser:height') {
        Field.claim();
        Field.complete(Math.max(
          document.body.scrollHeight,
          document.documentElement.scrollHeight,
          document.body.offsetHeight,
          document.documentElement.offsetHeight,
          document.documentElement.clientHeight
        ));
      }
    }
  }
})(jQuery);
