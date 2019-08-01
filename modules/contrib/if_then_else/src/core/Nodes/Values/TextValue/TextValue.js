class TextValueControl extends Rete.Control {

    constructor(emitter, key, readonly) {
        super(key);
        this.component = {
            props: ['ikey', 'getData', 'putData', 'emitter'],
            template: `<textarea class="input" type="text" @blur="change($event)" @dblclick.stop="" 
            v-model="value"></textarea>`,
            data() {
                return {
                    type: drupalSettings.if_then_else.nodes.text_value.type,
                    class: drupalSettings.if_then_else.nodes.text_value.class,
                    name: drupalSettings.if_then_else.nodes.text_value.name,
                    value: ''
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
                this.putData('type',drupalSettings.if_then_else.nodes.text_value.type);
                this.putData('class',drupalSettings.if_then_else.nodes.text_value.class);
                this.putData('name', drupalSettings.if_then_else.nodes.text_value.name);

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

    setValue(value) {
        this.vueContext.value = value;
    }
}