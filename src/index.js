let trips = JSON.parse(rezbs_custom_obj.all_trips);
const { createElement } = wp.element; //React.createElement
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.blockEditor;
const {
  TextControl,
  SelectControl,
  PanelBody,
  ServerSideRender,
  PanelRow,
} = wp.components;
import icons from "../js/icons.js";

registerBlockType("rezbs-cus-block/custom-rezbs", {
  title: "rezBS connect",
  icon: icons.custom,
  category: "common",
  attributes: {
    id: {
      type: "integer",
      default: 0,
    },
    class: {
      type: "string",
      default: "rezbs_button",
    },

    label: {
      type: "string",
      default: "Book Now!",
    },
    url: {
      type: "string",
      default: "",
    },
  },

  edit(props) {
    const { attributes, setAttributes } = props;

    function updateContent(event) {
      setAttributes({ class: event.target.value });
    }
    function updateLabel(event) {
      setAttributes({ label: event.target.value });
    }

    const onChangeTripId = (newTripId) => {
      setAttributes({ id: Number(newTripId) });

      for (let trip of trips) {
        if (newTripId == trip.value) {
          setAttributes({ url: trip.url });
        }
      }
      /* // Fetch the dynamic button URL based on the selected tripId
      fetchButtonUrl(Number(newTripId)); */
    };

    /*  const handleServerSideRender = () => {
      const data = {
          action: 'custom_block_render',
          attributes: JSON.stringify(attributes),
      };

      fetch(rezbs_custom_obj.ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data).toString(),
    })
    .then(response => response.json())
    .then(responseData => {
        // Handle the AJAX response
        console.log(responseData);
        dispatch('core/editor').updateBlock(props.clientId, { content: responseData.data });
    })
    .catch(error => {
        // Handle errors
        console.error(error);
    });

     
      
  }; */

    return createElement("div", { key: "rezbs_data_container" }, [
      //preview will go here
      //Deprecated code start
      /* createElement( ServerSideRender, {
                key:'rezbs_render_html',
                block: 'rezbs-cus-block/custom-rezbs',
                attributes: attributes
            } ), */
      //Deprecated code ends

      createElement("div", { key: "rezbs_render_html" }, [
        createElement(
          "a",
          {
            key: "rezbs_render_html_link",
            href: attributes.url,
            id: attributes.id,
            className: "button " + attributes.class,
          },
          attributes.label
        ),
      ]),
      //Block inspector
      createElement(InspectorControls, { key: "rezbs_1234343ins_ctr" }, [
        <PanelBody title={"Add RezBS Book Now Button"}>
          <div className="rezbs-text-container" key="rezbs-text-container">
            <PanelRow>
              <h2>Insert Booking Button</h2>
            </PanelRow>
            <PanelRow>
              <p>
                Select a Trip below to add the booking button to your post or
                page.
              </p>
            </PanelRow>
            <PanelRow>
              <p>
                You can assign a custom class to the button to match make the
                button match your existing theme.
              </p>
            </PanelRow>
            <PanelRow>
              <p>
                You can also change the default text shown on the button ("Book
                Now!").
              </p>
            </PanelRow>
          </div>
          <PanelRow>
            <SelectControl
              key="rezbs_select_ctr"
              label="Select a Trip"
              value={attributes.id}
              options={trips}
              onChange={(newval) => onChangeTripId(newval)}
            />
          </PanelRow>
          <PanelRow>
            <div
              className="rezbs-text-container-two"
              key="rezbs-text-container-two"
            >
              <TextControl
                key="rezbs_text_ctr_one"
                label="Add your own button class name"
                value={attributes.class}
                onChange={(newClass) => setAttributes({ class: newClass })}
              />
              <TextControl
                key="rezbs_text_ctr_two"
                label="Enter a custom Label for your button:"
                value={attributes.label}
                onChange={(newLabel) => setAttributes({ label: newLabel })}
              />
            </div>
          </PanelRow>
        </PanelBody>,
      ]),
    ]);
  },

  save() {
    return null;
  },
});
