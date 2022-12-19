!function(){"use strict";var e,t={734:function(){var e=window.wp.element,t=window.wp.blocks;var o=window.wp.i18n,n=window.wp.components,r=window.wp.blockEditor,l=window.wp.compose,a=function(t){let{icon:o,size:n=24,...r}=t;return(0,e.cloneElement)(o,{width:n,height:n,...r})},s=window.wp.primitives,i=(0,e.createElement)(s.SVG,{viewBox:"0 0 24 24",xmlns:"http://www.w3.org/2000/svg"},(0,e.createElement)(s.Path,{d:"M16 4.2v1.5h2.5v12.5H16v1.5h4V4.2h-4zM4.2 19.8h4v-1.5H5.8V5.8h2.5V4.2h-4l-.1 15.6zm5.1-3.1l1.4.6 4-10-1.4-.6-4 10z"})),c=JSON.parse('{"u2":"growtype/post"}'),p=(0,e.createElement)(s.SVG,{width:"35",height:"35",viewBox:"0 0 35 35",fill:"none",xmlns:"http://www.w3.org/2000/svg"},(0,e.createElement)("path",{d:"M0.579738 26.2702H9.31891C9.31891 28.1128 14.952 28.8498 14.952 24.5329V23.2168C13.5832 25.0067 12.2144 25.3752 10.0033 25.3752C3.15937 25.3752 -0.052009 20.9003 0.000636649 14.8987C0.0532823 8.89715 3.31731 4.47492 9.74008 4.52756C11.688 4.52756 13.7938 5.10667 15.2152 6.84397L15.3732 4.84344H24.007V24.5329C24.007 37.6417 0.579738 37.0626 0.579738 26.2702ZM9.21362 15.2146C9.21362 19.0578 14.8467 19.0578 14.8467 15.162C14.8467 11.2662 9.21362 11.2136 9.21362 15.2146Z",fill:"#315344"}));(0,t.registerBlockType)(c.u2,{icon:p,example:{attributes:{shortcode:"Growtype post"}},edit:function t(s){var c=s.attributes,p=s.setAttributes,u=(0,r.useBlockProps)(),h=(0,l.useInstanceId)(t),m="blocks-shortcode-input-".concat(h),_=function(e,t,o){var n,r,l;p((n={},r=e,l="custom"===o?t.selectedItem.value:t,r in n?Object.defineProperty(n,r,{value:l,enumerable:!0,configurable:!0,writable:!0}):n[r]=l,n));var a="[growtype_post";Object.entries(c).map((function(n){if("shortcode"!==n[0]){var r=n[0],l=n[1];if(r===e&&(l="custom"===o?t.selectedItem.value:t),"boolean"==typeof l&&(l=l?"true":"false"),"posts_per_page"!==r&&"columns"!==r||(l=l.toString()),"meta_query"===r)return;l.length>0&&(a+=" "+r+'="'+l+'"')}})),p({shortcode:a+="]"})};return 0!==Object.entries(c).length&&""!==c.shortcode||(c.shortcode="[growtype_post]"),(0,e.createElement)("div",u,(0,e.createElement)(r.InspectorControls,{key:"inspector"},(0,e.createElement)(n.Panel,null,(0,e.createElement)(n.PanelBody,{title:(0,o.__)("Main settings","growtype-post"),icon:"admin-plugins"},(0,e.createElement)(n.TextControl,{label:(0,o.__)("Post type","growtype-post"),help:(0,o.__)("Enter which post type should be used.","growtype-post"),onChange:function(e){return _("post_type",e)},value:c.post_type}),(0,e.createElement)(n.TextControl,{label:"Post in",help:(0,o.__)("Show only these posts. Enter ids separated by comma.","growtype-post"),onChange:function(e){return _("post__in",e)},value:c.post__in}),(0,e.createElement)(n.SelectControl,{label:"Order",help:(0,o.__)("How post should be ordered.","growtype-post"),options:[{label:"ASC",value:"asc"},{label:"DESC",value:"desc"}],value:c.order,onChange:function(e){return _("order",e)}}),(0,e.createElement)(n.SelectControl,{label:"Order by",help:(0,o.__)("According to what posts to should be ordered.","growtype-post"),options:[{label:"Date",value:"date"},{label:"Menu order",value:"menu_order"},{label:"Name",value:"name"}],value:c.orderby,onChange:function(e){return _("orderby",e)}}),(0,e.createElement)(n.ToggleControl,{label:"Post link",help:c.post_link?"Post is a link.":"Post is not a link.",checked:!!c.post_link,onChange:function(e){return _("post_link",e)}}),(0,e.createElement)(n.TextControl,{label:(0,o.__)("Parent class","growtype-post"),onChange:function(e){return _("parent_class",e)},value:c.id}),(0,e.createElement)(n.TextControl,{label:(0,o.__)("Parent ID","growtype-post"),onChange:function(e){return _("parent_id",e)},value:c.id})),(0,e.createElement)(n.PanelBody,{title:(0,o.__)("Preview settings","growtype-post"),icon:"admin-plugins"},(0,e.createElement)(n.RangeControl,{label:(0,o.__)("Columns","growtype-post"),help:(0,o.__)("How many columns in grid.","growtype-post"),value:c.columns,onChange:function(e){return _("columns",e)},min:1,max:8}),(0,e.createElement)(n.ToggleControl,{label:(0,o.__)("Show all posts"),checked:c.show_all_posts,onChange:function(e){return _("show_all_posts",e)}}),!c.show_all_posts&&(0,e.createElement)(n.RangeControl,{label:(0,o.__)("Posts per page"),value:c.posts_per_page,onChange:function(e){return _("posts_per_page",e)},min:1,max:50}),(0,e.createElement)(n.SelectControl,{label:"Post preview style",help:(0,o.__)("How post preview should look.","growtype-post"),options:[{label:"Basic",value:"basic"},{label:"Blog",value:"blog"},{label:"Content",value:"content"},{label:"Review",value:"review"},{label:"Testimonial",value:"testimonial"},{label:"Custom",value:"custom"}],value:c.preview_style,onChange:function(e){return _("preview_style",e)}}),"custom"===c.preview_style?(0,e.createElement)(n.TextControl,{label:(0,o.__)("Custom preview style","growtype-post"),help:(0,o.__)("Custom preview look.","growtype-post"),onChange:function(e){return _("preview_style_custom",e)},value:c.preview_style_custom}):"",(0,e.createElement)(n.__experimentalNumberControl,{label:"Intro content length",help:(0,o.__)("Post preview intro content text characters amount.","growtype-post"),isShiftStepEnabled:!1,onChange:function(e){return _("intro_content_length",e)},value:c.intro_content_length,min:1})),(0,e.createElement)(n.PanelBody,{title:(0,o.__)("Pagination settings","growtype-post"),icon:"admin-plugins"},(0,e.createElement)(n.PanelRow,null,(0,e.createElement)(n.ToggleControl,{label:"Active",help:c.pagination?"Pagination is active.":"Pagination is disabled.",checked:!!c.pagination,onChange:function(e){return _("pagination",e)}}))))),(0,e.createElement)(r.InspectorAdvancedControls,null,(0,e.createElement)(n.TextareaControl,{label:(0,o.__)("Meta Query","growtype-post"),help:(0,e.createElement)("a",{href:"https://wtools.io/convert-php-array-to-json",target:"_blank"},"Convert array to json here."),onChange:function(e){return p({meta_query:e})},value:c.meta_query})),(0,e.createElement)("div",(0,r.useBlockProps)({className:"components-placeholder"}),(0,e.createElement)("label",{htmlFor:m,className:"components-placeholder__label"},(0,e.createElement)(a,{icon:i}),(0,o.__)("Growtype post shortcode")),(0,e.createElement)(r.PlainText,{className:"blocks-shortcode__textarea",id:m,value:c.shortcode,"aria-label":(0,o.__)("Shortcode text"),placeholder:(0,o.__)("Write shortcode here…"),onChange:function(e){return p({shortcode:e})}})))},save:function(t){var o=t.attributes,n=r.useBlockProps.save();return(0,e.createElement)("div",n,o.shortcode)}})}},o={};function n(e){var r=o[e];if(void 0!==r)return r.exports;var l=o[e]={exports:{}};return t[e](l,l.exports,n),l.exports}n.m=t,e=[],n.O=function(t,o,r,l){if(!o){var a=1/0;for(p=0;p<e.length;p++){o=e[p][0],r=e[p][1],l=e[p][2];for(var s=!0,i=0;i<o.length;i++)(!1&l||a>=l)&&Object.keys(n.O).every((function(e){return n.O[e](o[i])}))?o.splice(i--,1):(s=!1,l<a&&(a=l));if(s){e.splice(p--,1);var c=r();void 0!==c&&(t=c)}}return t}l=l||0;for(var p=e.length;p>0&&e[p-1][2]>l;p--)e[p]=e[p-1];e[p]=[o,r,l]},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},function(){var e={826:0,431:0};n.O.j=function(t){return 0===e[t]};var t=function(t,o){var r,l,a=o[0],s=o[1],i=o[2],c=0;if(a.some((function(t){return 0!==e[t]}))){for(r in s)n.o(s,r)&&(n.m[r]=s[r]);if(i)var p=i(n)}for(t&&t(o);c<a.length;c++)l=a[c],n.o(e,l)&&e[l]&&e[l][0](),e[l]=0;return n.O(p)},o=self.webpackChunkplugin=self.webpackChunkplugin||[];o.forEach(t.bind(null,0)),o.push=t.bind(null,o.push.bind(o))}();var r=n.O(void 0,[431],(function(){return n(734)}));r=n.O(r)}();