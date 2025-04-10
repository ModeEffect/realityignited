const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
import Edit from './edit';

registerBlockType("memberpress/memberpress-download", {
  apiVersion: 2,
  title: __("Download", "memberpress"),
  icon: "download", // https://developer.wordpress.org/resource/dashicons/
  category: "memberpress",
  description: __("Display a MemberPress download.", "memberpress"),
  keywords: [__("membership download", "memberpress")],
  attributes: {
    download: { type: "string" },
    isList: { type: "boolean", default: true },
    listBy: { type: "string", default: "all" },
    limit: { type: "string" },
    category: { type: "string" },
    tag: { type: "string" },
    showDescription: { type: "boolean", default: false }
  },
  supports: {
    customClassName: false, // Removes "Custom CSS Class" from "Advanced" tab of block
    html: false // User cannot edit block as HTML
  },
  edit: Edit,
  save: function() {
    return null; // Null because we're rendering the output serverside
  }
});
