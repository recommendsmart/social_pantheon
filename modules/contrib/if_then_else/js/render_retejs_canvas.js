function jsUcfirst(string) {
	return string.charAt(0).toUpperCase() + string.slice(1);
}

// Initialize all event nodes.
let compatibleSockets = {};
let myComponents = [];
for (let nodeName in drupalSettings.if_then_else.nodes) {
	let node = drupalSettings.if_then_else.nodes[nodeName];

	let EventTriggerControl;
	if (!node.hasOwnProperty('control_class_name')) {
		EventTriggerControl = class extends Rete.Control {
			constructor(emitter, key) {
				super(key);
				this.component = {
					props: ['getData', 'putData'],
					data() {
						return node;
					},
					template: '',
					mounted() {
						// initialize variable for data
						this.putData('type', node.type);
						this.putData('class', node.class);
						this.putData('name', node.name);
					}
				};
			}
		};
	}
	else {
		EventTriggerControl = eval(node.control_class_name);
	}

	if (!node.hasOwnProperty('component_class_name')) {
		class EventComponent extends Rete.Component {
			constructor(){
				super(jsUcfirst(node.type) + ": " + node.label);
			}
			//Event node builder
			builder(eventNode) {
				eventNode.addControl(new EventTriggerControl(this.editor, nodeName));
				for (let name in node.inputs) {
					let inputLabel = node.inputs[name].label + (node.inputs[name].required ? ' *' : '');
					if (node.inputs[name].sockets.length === 1) {
						eventNode.addInput(new Rete.Input(name, inputLabel, sockets[node.inputs[name].sockets[0]]));
					}
					else if (node.inputs[name].sockets.length > 1) {
						let socketNames = [];
						let socketLabels = [];
						for (let idx in node.inputs[name].sockets) {
							socketNames.push(node.inputs[name].sockets[idx]);
							socketLabels.push(sockets[node.inputs[name].sockets[idx]].name);
						}
						socketNames.sort();
						socketLabels.sort();
						let socketLabel = socketLabels.join(', ');
						let socketName = socketNames.join(', ');

						if (!(socketName in sockets)) {
							sockets[socketName] = new Rete.Socket(socketLabel);
						}

						for (let idx in node.inputs[name].sockets) {
							if (!sockets[node.inputs[name].sockets[idx]].compatibleWith(sockets[socketName])) {
								sockets[node.inputs[name].sockets[idx]].combineWith(sockets[socketName]);
								if (typeof compatibleSockets[node.inputs[name].sockets[idx]] === "undefined") {
									compatibleSockets[node.inputs[name].sockets[idx]] = [];
								}
								compatibleSockets[node.inputs[name].sockets[idx]].push(socketName);
							}
						}

						eventNode.addInput(new Rete.Input(name, inputLabel, sockets[socketName]));
					}
				}
				for (let name in node.outputs) {
					eventNode.addOutput(new Rete.Output(name, node.outputs[name].label, sockets[node.outputs[name].socket]));
				}
			}
			worker(eventNode, inputs, outputs) {
				//outputs['form'] = eventNode.data.event;
			}
		}

		myComponents.push(new EventComponent());
	}else{
		myComponents.push(eval('new ' + node.component_class_name + '()'));
	}

	
}

// Go through all the output nodes, get their sockets, get those sockets' parent sockets and make them compatible.
for (let nodeName in drupalSettings.if_then_else.nodes) {
	let node = drupalSettings.if_then_else.nodes[nodeName];
	for (let name in node.outputs) {
		let socketName = node.outputs[name].socket;
		let parents = socketName.split('.');
		if (parents.length > 1) {
			let parentSocketName = '';
			for (let idx in parents) {
				if (parseInt(idx) === parents.length - 1) {
					continue;
				}

				if (idx > 0) {
					parentSocketName += '.';
				}
				parentSocketName += parents[idx];

				if (!sockets[socketName].compatibleWith(sockets[parentSocketName])) {
					sockets[socketName].combineWith(sockets[parentSocketName]);

					if (typeof compatibleSockets[parentSocketName] !== "undefined") {
						for (let idx2 in compatibleSockets[parentSocketName]) {
							if (!sockets[socketName].compatibleWith(sockets[compatibleSockets[parentSocketName][idx2]])) {
								sockets[socketName].combineWith(sockets[compatibleSockets[parentSocketName][idx2]]);
							}
						}
					}
				}
			}
		}
	}
}

editor.on("updateconnection", function(el, connection, points) {
  let outputSocketName = inverseSocketsMap[el.connection.output.socket.name];
  let inputSocketNames = el.connection.input.socket.name.split(", ");
  let compatible = false;
  for (let i = 0; i < inputSocketNames.length; i++) {
    let inputSocketName = inverseSocketsMap[inputSocketNames[i].trim()];
    if (inputSocketName === outputSocketName || compatibleSockets[outputSocketName].includes(inputSocketName)) {
       compatible = true;
       break;
    }
  }

  if (!compatible) {
    // Sockets are not compatible.
    editor.view.removeConnection(el.connection);
  }
});

for (let idx in myComponents) {
	editor.register(myComponents[idx]);
	engine.register(myComponents[idx]);
}

(function ($, Drupal, drupalSettings) {
	$(document).ready(function() {
		//If it is a new add rule page than this variable value will be false and it will trigger
		// a new retejs generation.
		if (!drupalSettings.if_then_else.data) {
			(async () => {
				editor.on('process nodecreated noderemoved connectioncreated connectionremoved', async () => {
						await engine.abort();
						await engine.process(editor.toJSON());
						//setting value of rule data from retejs graph
						jQuery('#ifthenelse-data').val(JSON.stringify(editor.toJSON()));
				});
		
				setTimeout(function(){ editor.view.resize(); }, 500);
				editor.view.resize();
				AreaPlugin.zoomAt(editor);
				editor.trigger('process');
		
			})();
		}
		else {
			//If it is a rule edit form than build the rete graph from saved data
			editor.fromJSON(JSON.parse(drupalSettings.if_then_else.data))
			.then(() => {
				editor.on("error", err => {
					alertify.error(err.message);
				});
		
				editor.on(
					"process connectioncreated connectionremoved nodecreated noderemoved",
					async function() {
						if (engine.silent) return;
						onMessageTask = [];
						await engine.abort();
						await engine.process(editor.toJSON());
						//setting value of rule data from retejs graph
						jQuery('#ifthenelse-data').val(JSON.stringify(editor.toJSON()));
					}
				);
		
				editor.trigger("process");
				editor.view.resize();
				AreaPlugin.zoomAt(editor);
			});
		}
	});
})(jQuery, Drupal, drupalSettings);
