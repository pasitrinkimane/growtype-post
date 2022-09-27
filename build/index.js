!function(){"use strict";var e,t={734:function(){var e=window.wp.blocks;var t=window.wp.element,n=window.wp.i18n,o=window.wp.components,l=window.wp.blockEditor,r=window.wp.compose,a=function(e){let{icon:n,size:o=24,...l}=e;return(0,t.cloneElement)(n,{width:o,height:o,...l})},s=window.wp.primitives,i=(0,t.createElement)(s.SVG,{viewBox:"0 0 24 24",xmlns:"http://www.w3.org/2000/svg"},(0,t.createElement)(s.Path,{d:"M16 4.2v1.5h2.5v12.5H16v1.5h4V4.2h-4zM4.2 19.8h4v-1.5H5.8V5.8h2.5V4.2h-4l-.1 15.6zm5.1-3.1l1.4.6 4-10-1.4-.6-4 10z"})),c=JSON.parse('{"u2":"growtype/post"}');(0,e.registerBlockType)(c.u2,{example:{attributes:{shortcode:"Growtype post"}},edit:function e(s){var c=s.attributes,u=s.setAttributes,p=(0,l.useBlockProps)(),d=(0,r.useInstanceId)(e),h="blocks-shortcode-input-".concat(d),m=function(e,t,n){var o,l,r;u((o={},l=e,r="custom"===n?t.selectedItem.value:t,l in o?Object.defineProperty(o,l,{value:r,enumerable:!0,configurable:!0,writable:!0}):o[l]=r,o));var a="[growtype_posts";Object.entries(c).map((function(o){if("shortcode"!==o[0]){var l=o[0],r=o[1];l===e&&(r="custom"===n?t.selectedItem.value:t),"boolean"==typeof r&&(r=r?"true":"false"),r.length>0&&(a+=" "+l+'="'+r+'"')}})),u({shortcode:a+="]"})};return console.log(c,"attributes - editing block"),console.log(Object.entries(c),"attributes length"),0!==Object.entries(c).length&&""!==c.shortcode||(c.shortcode="[growtype_posts]"),(0,t.createElement)("div",p,(0,t.createElement)(l.InspectorControls,{key:"setting"},(0,t.createElement)(o.Panel,null,(0,t.createElement)(o.PanelBody,{title:(0,n.__)("Main settings","wholesome-plugin"),icon:"admin-plugins"},(0,t.createElement)(o.PanelRow,null,(0,t.createElement)(o.TextControl,{label:(0,n.__)("Post type","growtype-post"),help:(0,n.__)("Enter which post type should be used.","growtype-post"),onChange:function(e){return m("post_type",e)},value:c.post_type})),(0,t.createElement)(o.PanelRow,null,(0,t.createElement)(o.TextControl,{label:(0,n.__)("Columns","growtype-post"),help:(0,n.__)("How many columns in grid.","growtype-post"),onChange:function(e){return m("columns",e)},value:c.columns})),(0,t.createElement)(o.PanelRow,null,(0,t.createElement)(o.TextControl,{label:(0,n.__)("Posts per page","growtype-post"),help:(0,n.__)("How many posts should be returned.","growtype-post"),onChange:function(e){return m("posts_per_page",e)},value:c.posts_per_page})),(0,t.createElement)(o.PanelRow,null,(0,t.createElement)(o.SelectControl,{label:"Post preview style",help:(0,n.__)("How post preview should look.","growtype-post"),options:[{label:"Basic",value:"basic"},{label:"Blog",value:"blog"},{label:"Content",value:"content"},{label:"Review",value:"review"},{label:"Testimonial",value:"testimonial"}],onChange:function(e){return m("preview_style",e)}})),(0,t.createElement)(o.PanelRow,null,(0,t.createElement)(o.SelectControl,{label:"Order",help:(0,n.__)("How post should be ordered.","growtype-post"),options:[{label:"ASC",value:"asc"},{label:"DESC",value:"desc"}],onChange:function(e){return m("order",e)}})),(0,t.createElement)(o.PanelRow,null,(0,t.createElement)(o.SelectControl,{label:"Order by",help:(0,n.__)("According to what posts to should be ordered.","growtype-post"),options:[{label:"Date",value:"date"},{label:"Menu order",value:"menu_order"},{label:"Name",value:"name"}],onChange:function(e){return m("orderby",e)}})),(0,t.createElement)(o.PanelRow,null,(0,t.createElement)(o.ToggleControl,{label:"Post link",help:c.post_link?"Post is a link.":"Post is not a link.",checked:!!c.post_link,onChange:function(e){return m("post_link",e)}})),(0,t.createElement)(o.PanelRow,null,(0,t.createElement)(o.TextControl,{label:(0,n.__)("Parent class","growtype-post"),onChange:function(e){return m("parent_class",e)},value:c.id})),(0,t.createElement)(o.PanelRow,null,(0,t.createElement)(o.TextControl,{label:(0,n.__)("Parent ID","growtype-post"),onChange:function(e){return m("parent_id",e)},value:c.id}))),(0,t.createElement)(o.PanelBody,{title:(0,n.__)("Slider settings","wholesome-plugin"),icon:"admin-plugins"},(0,t.createElement)(o.PanelRow,null,(0,t.createElement)(o.ToggleControl,{label:"Active",help:c.slider?"Showed in a slider.":"Showed without slider.",checked:!!c.slider,onChange:function(e){return m("slider",e)}})),(0,t.createElement)(o.PanelRow,null,(0,t.createElement)(o.__experimentalNumberControl,{label:"Slides amount to show",isShiftStepEnabled:!1,onChange:function(e){return m("slider_slides_amount_to_show",e)},value:c.slider_slides_amount_to_show,min:1}))))),(0,t.createElement)("div",(0,l.useBlockProps)({className:"components-placeholder"}),(0,t.createElement)("label",{htmlFor:h,className:"components-placeholder__label"},(0,t.createElement)(a,{icon:i}),(0,n.__)("Growtype post shortcode")),(0,t.createElement)(l.PlainText,{className:"blocks-shortcode__textarea",id:h,value:c.shortcode,"aria-label":(0,n.__)("Shortcode text"),placeholder:(0,n.__)("Write shortcode here…"),onChange:function(e){return u({shortcode:e})}})))},save:function(e){var n=e.attributes,o=l.useBlockProps.save();return(0,t.createElement)("div",o,n.shortcode)}})}},n={};function o(e){var l=n[e];if(void 0!==l)return l.exports;var r=n[e]={exports:{}};return t[e](r,r.exports,o),r.exports}o.m=t,e=[],o.O=function(t,n,l,r){if(!n){var a=1/0;for(u=0;u<e.length;u++){n=e[u][0],l=e[u][1],r=e[u][2];for(var s=!0,i=0;i<n.length;i++)(!1&r||a>=r)&&Object.keys(o.O).every((function(e){return o.O[e](n[i])}))?n.splice(i--,1):(s=!1,r<a&&(a=r));if(s){e.splice(u--,1);var c=l();void 0!==c&&(t=c)}}return t}r=r||0;for(var u=e.length;u>0&&e[u-1][2]>r;u--)e[u]=e[u-1];e[u]=[n,l,r]},o.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},function(){var e={826:0,431:0};o.O.j=function(t){return 0===e[t]};var t=function(t,n){var l,r,a=n[0],s=n[1],i=n[2],c=0;if(a.some((function(t){return 0!==e[t]}))){for(l in s)o.o(s,l)&&(o.m[l]=s[l]);if(i)var u=i(o)}for(t&&t(n);c<a.length;c++)r=a[c],o.o(e,r)&&e[r]&&e[r][0](),e[r]=0;return o.O(u)},n=self.webpackChunkplugin=self.webpackChunkplugin||[];n.forEach(t.bind(null,0)),n.push=t.bind(null,n.push.bind(n))}();var l=o.O(void 0,[431],(function(){return o(734)}));l=o.O(l)}();