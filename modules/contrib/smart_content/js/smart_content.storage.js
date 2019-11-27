(function ($) {

  var browserStorage = function () {
    this.bin = JSON.parse(window.localStorage.getItem('_scs'));
    if(this.bin === null) {
      window.localStorage.setItem('_scs', JSON.stringify({}));
    }

    this.bin = JSON.parse(window.localStorage.getItem('_scs'));
  };

  browserStorage.prototype.getFieldValue = function (Field) {
    return this.getValue(Field.pluginId, 'fields');
  };

  browserStorage.prototype.setFieldValue = function (Field, value, maxAge) {
    this.setValue(Field.pluginId, value, maxAge, 'fields');
  };

  browserStorage.prototype.isFieldExpired = function (Field) {
    //@todo: add invoke so modules can do special processing.
    return this.isExpired(Field.pluginId, 'fields');
  };

  browserStorage.prototype.getValue = function (name, bin) {
    var bin = bin !== undefined ? bin : 'default';
    return (this.bin.hasOwnProperty(bin) && this.bin[bin].hasOwnProperty(name) ? this.bin[bin][name] : null);
  };

  browserStorage.prototype.setValue = function (name, value, maxAge, bin) {
    var maxAge = maxAge !== undefined ? maxAge : 50000;

    var bin = bin !== undefined ? bin : 'default';

    if(maxAge === -1) {
      maxAge = 50 * 365 * 24 * 60 * 60 * 1000;
    }

    var expiration = new Date(Date.now() + maxAge);
    this.bin[bin] = this.bin[bin] || {} ;
    this.bin[bin][name] = {
      name: name,
      value: value,
      expiration: expiration.toUTCString(),
    }

    window.localStorage.setItem('_scs', JSON.stringify(this.bin));
  }


  browserStorage.prototype.isExpired = function (name, bin) {
    var value = this.getValue(name, bin);
    if(value !== null) {
      return new Date() > new Date(value.expiration);
    }
    return true;
  }


  Drupal.smart_content.storage = new browserStorage;


})(jQuery);
