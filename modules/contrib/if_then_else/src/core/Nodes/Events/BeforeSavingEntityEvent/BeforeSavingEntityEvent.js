class BeforeSavingEntityEventControl extends Rete.Control {

  constructor(emitter, key, onChange) {
    super(key);
    this.component = {
      components: {
        // Component included for Multiselect.
        Multiselect: window.VueMultiselect.default
      },
      props: ['emitter', 'ikey', 'getData', 'putData', 'onChange'],
      template: `
    <div class="fields-container">
      <div class="label">Match Condition</div>

      <div class="radio">
        <input type="radio" :id="radio1_uid" value="list" v-model="selection" @change="selectionChanged">
        <label :for="radio1_uid">Select An Entity</label>
      </div>
      
      <div v-if="selection === 'list'">
        <div class="entity-select">
          <label class="typo__label">Entity</label>
          <multiselect @wheel.native.stop="wheel" v-model="selected_entity" :show-labels="false" :options="entities" 
          placeholder="Entity" @input="entitySelected" label="label" 
          track-by="value"></multiselect></div><div class="bundle-select" v-if="showBundleList">
        
          <label class="typo__label">Bundle</label>
          <multiselect @wheel.native.stop="wheel" v-model="selected_bundle" :options="bundles" :show-labels="false" 
          placeholder="Bundle" @input="bundleSelected" label="label" 
          track-by="value"></multiselect>
        </div>
      </div>

      <div class="radio">
        <input type="radio" :id="radio2_uid" value="all" v-model="selection" @change="selectionChanged">
        <label :for="radio2_uid">All Entities</label>
      </div>
      
    </div>`,
      data() {
        return {
          type: drupalSettings.if_then_else.nodes.before_saving_entity_event.type,
          class: drupalSettings.if_then_else.nodes.before_saving_entity_event.class,
          name: drupalSettings.if_then_else.nodes.before_saving_entity_event.name,
          classArg: drupalSettings.if_then_else.nodes.before_saving_entity_event.classArg,
          value: 0,
          showBundleList: true,
          entities: [],
          bundles: [],
          selection: 'list',
          selected_entity: [],
          selected_bundle: [],
          radio1_uid: '',
          radio2_uid: '',
        }
      },
      methods: {
        update() {
          //Triggered on focus out of formclass input field
          if (this.ikey)
            this.putData(this.ikey, this.value);

          //This is called to reprocess the retejs editor
          editor.trigger('process');
        },
        entitySelected(value) {
          //Triggered when selecting an entity from entity dropdown.
          //reinitialize all values
          this.bundles = [];
          this.selected_bundle = [];
          this.bundleSelected();
          this.selected_entity = [];
          if (value !== undefined && value !== null && value !== ''){ //check if an entity is selected
            let entity_id = value.value;
            this.selected_entity = {label: value.label, value: value.value};
            //This value is passed from module.
            let bundle_list = drupalSettings.if_then_else.nodes.before_saving_entity_event.entity_info[entity_id]['bundles'];
            this.showBundleList = true;

            Object.keys(bundle_list).forEach(itemKey => {
              this.bundles.push({label: bundle_list[itemKey].label, value: bundle_list[itemKey].bundle_id});
            });
          }else {
            this.putData('selected_bundle',[]);
          }

          //Updating reactive variable of Vue to reflect changes on frontend
          this.putData('selected_entity',this.selected_entity);
          editor.trigger('process');
        },
        bundleSelected(){
          //Triggered when a bundle is selected. We are fetching fields using ajax in this function
          this.showLoadingSpinner = false;

          this.putData('selected_bundle',this.selected_bundle);
          if(this.selected_entity != undefined && typeof this.selected_entity != 'undefined' && this.selected_entity.value !== '' && this.selected_bundle != undefined && typeof this.selected_bundle != 'undefined' && this.selected_bundle !== '') {
            this.onChange(this.selected_entity.value, this.selected_bundle.value);
          }
          editor.trigger('process');
        },
        selectionChanged() {
          this.putData('selection', this.selection);
          if (typeof this.selected_entity != 'undefined' && this.selected_entity.value !== '' && this.selected_bundle != undefined && typeof this.selected_bundle != 'undefined' && this.selected_bundle !== '') {
            this.onChange(this.selected_entity.value, this.selected_bundle.value, this.selection);
          } else {
            this.onChange('', '', this.selection);
          }
          editor.trigger('process');
        }
      },
      mounted() {
        //Triggered when loading retejs editor. See documentaion of Vuejs

        //initialize variable for data
        this.putData('type', drupalSettings.if_then_else.nodes.before_saving_entity_event.type);
        this.putData('class', drupalSettings.if_then_else.nodes.before_saving_entity_event.class);
        this.putData('name', drupalSettings.if_then_else.nodes.before_saving_entity_event.name);
        this.putData('classArg', drupalSettings.if_then_else.nodes.before_saving_entity_event.classArg);
        
        //Setting values of retejs condition nodes when editing rule page loads
        this.selected_entity = this.getData('selected_entity');
        this.selected_bundle = this.getData('selected_bundle');
        if(this.selected_entity != undefined && typeof this.selected_entity != 'undefined' && this.selected_entity !== '' && this.selected_bundle != undefined && typeof this.selected_bundle != 'undefined' && this.selected_bundle !== '') {
          this.onChange(this.selected_entity.value, this.selected_bundle.value);
        }
        this.selection = this.getData('selection');
      },
      created() {
        //Triggered when loading retejs editor but before mounted function. See documentaion of Vuejs
        this.radio1_uid = _.uniqueId('radio_');
        this.radio2_uid = _.uniqueId('radio_');

        //Fetching values of fields when editing rule page loads
        if(drupalSettings.if_then_else.nodes.before_saving_entity_event.entity_info){
          var entities_list = drupalSettings.if_then_else.nodes.before_saving_entity_event.entity_info;
          Object.keys(entities_list).forEach(itemKey => {
            this.entities.push({label: entities_list[itemKey].label, value: entities_list[itemKey].entity_id});
          });

          // Load the bundle list when form loads for edit
          this.selected_entity = this.getData('selected_entity');
          if(this.selected_entity != undefined && typeof this.selected_entity != 'undefined' && this.selected_entity !== ''){
            let selected_entity = this.selected_entity.value;
            if (drupalSettings.if_then_else.nodes.before_saving_entity_event.entity_info) {
              let bundle_list = drupalSettings.if_then_else.nodes.before_saving_entity_event.entity_info[selected_entity]['bundles'];
              Object.keys(bundle_list).forEach(itemKey => {
                this.bundles.push({label: bundle_list[itemKey].label, value: bundle_list[itemKey].bundle_id});
              });
            }
          }
        }
      }
    };
    this.props = { emitter, ikey: key, onChange };
  }

  setValue(val) {
    this.vueContext.value = val;
  }
}

class BeforeSavingEntityEventComponent extends Rete.Component {
  constructor() {
    var nodeName = 'before_saving_entity_event';
    var node = drupalSettings.if_then_else.nodes[nodeName];
    super(jsUcfirst(node.type) + ": " + node.label);
  }

  //Event node builder
  builder(eventNode) {

    var node_outputs = [];
    var nodeName = 'before_saving_entity_event';
    var node = drupalSettings.if_then_else.nodes[nodeName];
    node_outputs['success'] = new Rete.Output('success', 'Success', sockets['bool']);
    node_outputs['success']['description'] = node.outputs['success'].description;

    node_outputs['entity'] = new Rete.Output('entity', 'Entity', sockets['object.entity']);
    node_outputs['entity']['description'] = node.outputs['entity'].description;

    node_outputs['entity_original'] = new Rete.Output('entity_original', 'Entity Original', sockets['object.entity']);
    node_outputs['entity_original']['description'] = node.outputs['entity_original'].description;

    eventNode.addOutput(node_outputs['success']);
    eventNode.addOutput(node_outputs['entity']);
    eventNode.addOutput(node_outputs['entity_original']);

    var nodeName = 'before_saving_entity_event';
    var node = drupalSettings.if_then_else.nodes[nodeName];

    function handleInput() {
      return function(entityValue, bundleValue, selection) {
        let socket_out = eventNode.outputs.get('entity');
        let socket_out_original = eventNode.outputs.get('entity_original');

        if (selection == 'all') {
          socket_out.socket = sockets['object.entity'];
          socket_out_original.socket = sockets['object.entity'];
          eventNode.outputs.set('entity', socket_out);
          eventNode.outputs.set('entity_original', socket_out_original);
        } else {
          if (entityValue != undefined && typeof entityValue != 'undefined' && entityValue !== '' && bundleValue != undefined && typeof bundleValue != 'undefined' && bundleValue !== '') {
            socket_out.socket = sockets['object.entity.' + entityValue + '.' + bundleValue];
            socket_out_original.socket = sockets['object.entity.' + entityValue + '.' + bundleValue];
            makeCompatibleSocketsByName('object.entity.' + entityValue + '.' + bundleValue);
  
            eventNode.outputs.set('entity', socket_out);
            eventNode.outputs.set('entity_original', socket_out_original);
          }
        }
        eventNode.update();
        editor.view.updateConnections({
          node: eventNode
        });
        editor.trigger('process');
      }
    }

    eventNode.addControl(new BeforeSavingEntityEventControl(this.editor, nodeName, handleInput()));
    eventNode['description'] = node.description;

  }
  worker(eventNode, inputs, outputs) {
    //outputs['form'] = eventNode.data.event;
  }
}
