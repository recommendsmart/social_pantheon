class GetArrayIndexValueActionControl extends Rete.Control {

  constructor(emitter, key, readonly) {
      super(key);
      this.component = {
          props: ['ikey', 'getData', 'putData', 'emitter'],
          template: `<div class="fields-container">
              <input class="input" type="text"  v-model="value" @blur="change($event)" @dblclick.stop="" />            
          </div>`,
          data() {
              return {
                  type: drupalSettings.if_then_else.nodes.get_entity_field_action.type,
                  class: drupalSettings.if_then_else.nodes.get_entity_field_action.class,
                  name: drupalSettings.if_then_else.nodes.get_entity_field_action.name,
                  value: '',
                popupActive:false,
              }
          },
          methods: {
              change(e) {
                  this.value = e.target.value;
                  this.update();
              },
              update() {
                  if (this.ikey) {
                      this.putData('value', this.value);
                  }
                  editor.trigger('process');
              }
          },
          mounted() {
              this.putData('type',drupalSettings.if_then_else.nodes.get_entity_field_action.type);
              this.putData('class',drupalSettings.if_then_else.nodes.get_entity_field_action.class);
              this.putData('name', drupalSettings.if_then_else.nodes.get_entity_field_action.name);

              var get_value = this.getData('value');
              if (typeof get_value != 'undefined') {
                  this.value = get_value;
              }
              else {
                  this.value = '';
              }
          }
      };
      this.props = { emitter, ikey: key, readonly };
  }
}
