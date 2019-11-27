(function ($, Drupal) {

  var SmartContentManager = Drupal.smart_content.SmartContentManager;
  SmartContentManager.condition_type = SmartContentManager.condition_type || {};
  SmartContentManager.condition_type['type:textfield'] = SmartContentManager.condition_type['type:textfield'] || {};
  SmartContentManager.condition_type['type:textfield'].ConditionTypeStandard = {
    evaluate: function (values, context) {
      switch (values['op']) {
        case 'equals':
          return (context != null) && (String(context.toLowerCase()) === values['value'].toLowerCase());

        case 'starts_with':
          return (context != null) && (String(context).toLowerCase().substring(0, values['value'].length) === values['value'].toLowerCase());

        case 'empty':
          return (context != null) && (context.length === 0);
      }
    }
  };

  SmartContentManager.condition_type['type:key_value'] = SmartContentManager.condition_type['type:key_value'] || {};
  SmartContentManager.condition_type['type:key_value'].ConditionTypeStandard = {
    evaluate: function (values, context) {
      switch (values['op']) {
        case 'equals':
          return (context != null) && (String(context.toLowerCase()) === values['value'].toLowerCase());

        case 'starts_with':
          return (context != null) && (String(context).toLowerCase().substring(0, values['value'].length) === values['value'].toLowerCase());

        case 'empty':
          return (context != null) && (context.length === 0);

        case 'is_set':
          return (context != null);

      }
    }
  };

  SmartContentManager.condition_type['type:boolean'] = SmartContentManager.condition_type['type:boolean'] || {};
  SmartContentManager.condition_type['type:boolean'].ConditionTypeStandard = {
    evaluate: function (values, context) {
      return Boolean(context);
    }
  };

  SmartContentManager.condition_type['type:number'] = SmartContentManager.condition_type['type:number'] || {};
  SmartContentManager.condition_type['type:number'].ConditionTypeStandard = {
    evaluate: function (values, context) {
      switch (values['op']) {
        case 'equals':
          return (context != null) && (Number(context) === Number(values['value']));

        case 'gt':
          return (context != null) && (Number(context) > Number(values['value']));

        case 'lt':
          return (context != null) && (Number(context) < Number(values['value']));

        case 'gte':
          return (context != null) && (Number(context) >= Number(values['value']));

        case 'lte':
          return (context != null) && (Number(context) <= Number(values['value']));
      }
    }
  };

  SmartContentManager.condition_type['type:value'] = SmartContentManager.condition_type['type:value'] || {};
  SmartContentManager.condition_type['type:value'].ConditionTypeStandard = {
    evaluate: function (values, context) {
      return context;
    }
  };

  SmartContentManager.condition_type['type:select'] = SmartContentManager.condition_type['type:select'] || {};
  SmartContentManager.condition_type['type:select'].ConditionTypeStandard = {
    evaluate: function (values, context) {
      return values['value'] === context;
    }
  }
})(jQuery, Drupal);
