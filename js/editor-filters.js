"use strict";function _typeof(e){return(_typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function _slicedToArray(e,t){return _arrayWithHoles(e)||_iterableToArrayLimit(e,t)||_unsupportedIterableToArray(e,t)||_nonIterableRest()}function _nonIterableRest(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}function _unsupportedIterableToArray(e,t){var r;if(e)return"string"==typeof e?_arrayLikeToArray(e,t):"Map"===(r="Object"===(r={}.toString.call(e).slice(8,-1))&&e.constructor?e.constructor.name:r)||"Set"===r?Array.from(e):"Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)?_arrayLikeToArray(e,t):void 0}function _arrayLikeToArray(e,t){(null==t||t>e.length)&&(t=e.length);for(var r=0,n=Array(t);r<t;r++)n[r]=e[r];return n}function _iterableToArrayLimit(e,t){var r=null==e?null:"undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(null!=r){var n,o,i,l,a=[],c=!0,p=!1;try{if(i=(r=r.call(e)).next,0===t){if(Object(r)!==r)return;c=!1}else for(;!(c=(n=i.call(r)).done)&&(a.push(n.value),a.length!==t);c=!0);}catch(e){p=!0,o=e}finally{try{if(!c&&null!=r.return&&(l=r.return(),Object(l)!==l))return}finally{if(p)throw o}}return a}}function _arrayWithHoles(e){if(Array.isArray(e))return e}function ownKeys(t,e){var r,n=Object.keys(t);return Object.getOwnPropertySymbols&&(r=Object.getOwnPropertySymbols(t),e&&(r=r.filter(function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable})),n.push.apply(n,r)),n}function _objectSpread(t){for(var e=1;e<arguments.length;e++){var r=null!=arguments[e]?arguments[e]:{};e%2?ownKeys(Object(r),!0).forEach(function(e){_defineProperty(t,e,r[e])}):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(r)):ownKeys(Object(r)).forEach(function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(r,e))})}return t}function _defineProperty(e,t,r){return(t=_toPropertyKey(t))in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}function _toPropertyKey(e){e=_toPrimitive(e,"string");return"symbol"==_typeof(e)?e:e+""}function _toPrimitive(e,t){if("object"!=_typeof(e)||!e)return e;var r=e[Symbol.toPrimitive];if(void 0===r)return("string"===t?String:Number)(e);r=r.call(e,t||"default");if("object"!=_typeof(r))return r;throw new TypeError("@@toPrimitive must return a primitive value.")}var addFilter=wp.hooks.addFilter,createHigherOrderComponent=wp.compose.createHigherOrderComponent,_wp$element=wp.element,Fragment=_wp$element.Fragment,useEffect=_wp$element.useEffect,useState=_wp$element.useState,_wp$blockEditor=wp.blockEditor,InspectorControls=_wp$blockEditor.InspectorControls,MediaUpload=_wp$blockEditor.MediaUpload,MediaUploadCheck=_wp$blockEditor.MediaUploadCheck,_wp$components=wp.components,PanelBody=_wp$components.PanelBody,Button=_wp$components.Button,__=wp.i18n.__,_wp$blocks=wp.blocks,registerBlockType=_wp$blocks.registerBlockType,unregisterBlockVariation=_wp$blocks.unregisterBlockVariation,ezpzListItemImageAttribute=function(e,t){return"core/list-item"===t&&(e.attributes=_objectSpread(_objectSpread({},e.attributes),{},{imageId:{type:"number",default:0}})),e},ezpzAddListElementImage=(addFilter("blocks.registerBlockType","ezpz/core/list-item/attributes",ezpzListItemImageAttribute),createHigherOrderComponent(function(l){return function(e){var t,r,n,o=e.attributes.imageId,i=e.setAttributes;return"core/list-item"!==e.name?React.createElement(l,e):(r=(t=_slicedToArray(useState(""),2))[0],n=t[1],useEffect(function(){o?new wp.media.model.Attachment({id:o}).fetch().then(function(e){console.log(e),n(e.sizes.thumbnail.url)}):n("")},[o]),void 0===o&&i({imageId:""}),React.createElement(Fragment,null,React.createElement(l,e),React.createElement(InspectorControls,null,React.createElement(PanelBody,{title:__("Bullet Point Image","lemonjelly"),initialOpen:!0},React.createElement("p",{style:{fontSize:"12px",fontStyle:"normal",color:"rgb(117, 117, 117)"}},__("If you would like to replace the bullet point with an image, please specify the image here. Your image will be cropped and scaled to a square shape on the front end.","lemonjelly")),React.createElement(MediaUploadCheck,null,o?React.createElement("div",null,React.createElement("img",{src:r,alt:__("Selected image","lemonjelly"),style:{width:"60px",marginBottom:"10px",maxWidth:"100%",display:"block"}}),React.createElement(Button,{isSecondary:!0,onClick:function(){return i({imageId:""})},style:{marginRight:"5px"}},__("Remove Image","lemonjelly")),React.createElement(MediaUpload,{onSelect:function(e){return i({imageId:e.id})},allowedTypes:["image"],render:function(e){e=e.open;return React.createElement(Button,{isPrimary:!0,onClick:e},__("Replace Image","lemonjelly"))}})):React.createElement(MediaUpload,{onSelect:function(e){return i({imageId:e.id})},allowedTypes:["image"],render:function(e){e=e.open;return React.createElement(Button,{isPrimary:!0,onClick:e},__("Upload / Select Image","lemonjelly"))}}))))))}},"ezpzAddListElementImage"));addFilter("editor.BlockEdit","ezpz/core/list-item/inspector-controls",ezpzAddListElementImage),wp.domReady(function(){var e=wp.element.createElement(wp.primitives.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 48 48"},wp.element.createElement(wp.primitives.Path,{d:"M39,12H9c-1.1,0-2,0.9-2,2v20c0,1.1,0.9,2,2,2h30c1.1,0,2-0.9,2-2V14C41,12.9,40.1,12,39,12z M9,34V14h1.7h2.7 v20h-2.7H9z M17,34h-1.6V14H17h1h1.8v20H18H17z M23,34h-1.2V14H23h2h1.2v20H25H23z M28.2,34V14H31h1.6v20H31H28.2z M39,34h-4.4V14 H39V34z"})),t=wp.element.createElement(wp.primitives.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 48 48"},wp.element.createElement(wp.primitives.Path,{d:"M39,12H9c-1.1,0-2,0.9-2,2v20c0,1.1,0.9,2,2,2h30c1.1,0,2-0.9,2-2V14C41,12.9,40.1,12,39,12z M9,34V14h1.7h1.6 v20h-1.6H9z M15,34h-0.7V14H15h2h0.7v20H17H15z M19.7,34V14H20h3v20h-3H19.7z M25,14h3.3v20H25V14z M33,34h-2h-0.7V14H31h2h0.7v20 H33z M39,34h-3.3V14H39V34z"}));wp.blocks.registerBlockVariation("ezpz/columns",{name:"five-columns-equal",title:__("5 columns"),description:__("Five columns; equal split"),icon:e,innerBlocks:[["ezpz/column",{width:"20%"}],["ezpz/column",{width:"20%"}],["ezpz/column",{width:"20%"}],["ezpz/column",{width:"20%"}],["ezpz/column",{width:"20%"}]],scope:["block"]}),wp.blocks.registerBlockVariation("ezpz/columns",{name:"six-columns-equal",title:__("6 columns"),description:__("Six columns; equal split"),icon:t,innerBlocks:[["ezpz/column",{width:"16.66%"}],["ezpz/column",{width:"16.66%"}],["ezpz/column",{width:"16.66%"}],["ezpz/column",{width:"16.66%"}],["ezpz/column",{width:"16.66%"}],["ezpz/column",{width:"16.66%"}]],scope:["block"]})});