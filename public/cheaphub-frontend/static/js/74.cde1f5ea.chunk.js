(this.webpackJsonpSeamless=this.webpackJsonpSeamless||[]).push([[74],{1624:function(e,t,c){"use strict";c.r(t);var s=c(20),n=c(488),l=c(1),a=c(7),r=c.n(a),i=c(2),d=c.n(i),b=c(1046),j=c(887),o=c(491),h=c(489),m=c(96),x=(c(807),c(14));t.default=function(){var e=Object(l.useState)(null),t=Object(s.a)(e,2),c=t[0],a=t[1];Object(l.useEffect)((function(){r.a.get("/blog/list/data").then((function(e){return a(e.data)}))}),[]);var i={Quote:"light-info",Fashion:"light-primary",Gaming:"light-danger",Video:"light-warning",Food:"light-success"};return Object(x.jsxs)(l.Fragment,{children:[Object(x.jsx)(h.a,{breadCrumbTitle:"Blog List",breadCrumbParent:"Pages",breadCrumbParent2:"Blog",breadCrumbActive:"List"}),Object(x.jsxs)("div",{className:"blog-wrapper",children:[Object(x.jsx)("div",{className:"content-detached content-left",children:Object(x.jsx)("div",{className:"content-body",children:null!==c?Object(x.jsxs)("div",{className:"blog-list-wrapper",children:[Object(x.jsx)(m.ib,{children:c.map((function(e){return Object(x.jsx)(m.B,{md:"6",children:Object(x.jsxs)(m.l,{children:[Object(x.jsx)(n.b,{to:"/pages/blog/detail/".concat(e.id),children:Object(x.jsx)(m.q,{className:"img-fluid",src:e.img,alt:e.title,top:!0})}),Object(x.jsxs)(m.m,{children:[Object(x.jsx)(m.v,{tag:"h4",children:Object(x.jsx)(n.b,{className:"blog-title-truncate text-body-heading",to:"/pages/blog/detail/".concat(e.id),children:e.title})}),Object(x.jsxs)("div",{className:"d-flex",children:[Object(x.jsx)(o.a,{className:"me-50",img:e.avatar,imgHeight:"24",imgWidth:"24"}),Object(x.jsxs)("div",{children:[Object(x.jsx)("small",{className:"text-muted me-25",children:"by"}),Object(x.jsx)("small",{children:Object(x.jsx)("a",{className:"text-body",href:"/",onClick:function(e){return e.preventDefault()},children:e.userFullName})}),Object(x.jsx)("span",{className:"text-muted ms-50 me-25",children:"|"}),Object(x.jsx)("small",{className:"text-muted",children:e.blogPosted})]})]}),Object(x.jsx)("div",{className:"my-1 py-25",children:e.tags.map((function(t,c){return Object(x.jsx)("a",{href:"/",onClick:function(e){return e.preventDefault()},children:Object(x.jsx)(m.f,{className:d()({"me-50":c!==e.tags.length-1}),color:i[t],pill:!0,children:t})},c)}))}),Object(x.jsx)(m.u,{className:"blog-content-truncate",children:e.excerpt}),Object(x.jsx)("hr",{}),Object(x.jsxs)("div",{className:"d-flex justify-content-between align-items-center",children:[Object(x.jsxs)(n.b,{to:"/pages/blog/detail/".concat(e.id),children:[Object(x.jsx)(b.a,{size:15,className:"text-body me-50"}),Object(x.jsxs)("span",{className:"text-body fw-bold",children:[e.comment," Comments"]})]}),Object(x.jsx)(n.b,{className:"fw-bold",to:"/pages/blog/detail/".concat(e.id),children:"Read More"})]})]})]})},e.title)}))}),Object(x.jsx)(m.ib,{children:Object(x.jsx)(m.B,{sm:"12",children:Object(x.jsxs)(m.bb,{className:"d-flex justify-content-center mt-2",children:[Object(x.jsx)(m.cb,{className:"prev-item",children:Object(x.jsx)(m.db,{href:"#",onClick:function(e){return e.preventDefault()}})}),Object(x.jsx)(m.cb,{children:Object(x.jsx)(m.db,{href:"#",onClick:function(e){return e.preventDefault()},children:"1"})}),Object(x.jsx)(m.cb,{children:Object(x.jsx)(m.db,{href:"#",onClick:function(e){return e.preventDefault()},children:"2"})}),Object(x.jsx)(m.cb,{children:Object(x.jsx)(m.db,{href:"#",onClick:function(e){return e.preventDefault()},children:"3"})}),Object(x.jsx)(m.cb,{active:!0,children:Object(x.jsx)(m.db,{href:"#",onClick:function(e){return e.preventDefault()},children:"4"})}),Object(x.jsx)(m.cb,{children:Object(x.jsx)(m.db,{href:"#",onClick:function(e){return e.preventDefault()},children:"5"})}),Object(x.jsx)(m.cb,{children:Object(x.jsx)(m.db,{href:"#",onClick:function(e){return e.preventDefault()},children:"6"})}),Object(x.jsx)(m.cb,{children:Object(x.jsx)(m.db,{href:"#",onClick:function(e){return e.preventDefault()},children:"7"})}),Object(x.jsx)(m.cb,{className:"next-item",children:Object(x.jsx)(m.db,{href:"#",onClick:function(e){return e.preventDefault()}})})]})})})]}):null})}),Object(x.jsx)(j.a,{})]})]})}},489:function(e,t,c){"use strict";var s=c(488),n=c(96),l=c(14);t.a=function(e){var t=e.breadCrumbTitle,c=e.breadCrumbParent,a=e.breadCrumbParent2,r=e.breadCrumbParent3,i=e.breadCrumbActive;return Object(l.jsx)("div",{className:"content-header row",children:Object(l.jsx)("div",{className:"content-header-left col-md-9 col-12 mb-2",children:Object(l.jsx)("div",{className:"row breadcrumbs-top",children:Object(l.jsxs)("div",{className:"col-12",children:[t?Object(l.jsx)("h2",{className:"content-header-title float-start mb-0",children:t}):"",Object(l.jsx)("div",{className:"breadcrumb-wrapper vs-breadcrumbs d-sm-block d-none col-12",children:Object(l.jsxs)(n.g,{children:[Object(l.jsx)(n.h,{tag:"li",children:Object(l.jsx)(s.b,{to:"/",children:"Home"})}),Object(l.jsx)(n.h,{tag:"li",className:"text-primary",children:c}),a?Object(l.jsx)(n.h,{tag:"li",className:"text-primary",children:a}):"",r?Object(l.jsx)(n.h,{tag:"li",className:"text-primary",children:r}):"",Object(l.jsx)(n.h,{tag:"li",active:!0,children:i})]})})]})})})})}},807:function(e,t,c){},887:function(e,t,c){"use strict";var s=c(20),n=c(488),l=c(1),a=c(7),r=c.n(a),i=c(2),d=c.n(i),b=c(487),j=c(962),o=c(491),h=c(96),m=c(14);t.a=function(){var e=Object(l.useState)(null),t=Object(s.a)(e,2),c=t[0],a=t[1];Object(l.useEffect)((function(){r.a.get("/blog/list/data/sidebar").then((function(e){return a(e.data)}))}),[]);var i={Quote:"light-info",Fashion:"light-primary",Gaming:"light-danger",Video:"light-warning",Food:"light-success"};return Object(m.jsx)("div",{className:"sidebar-detached sidebar-right",children:Object(m.jsx)("div",{className:"sidebar",children:Object(m.jsx)("div",{className:"blog-sidebar right-sidebar my-2 my-lg-0",children:Object(m.jsxs)("div",{className:"right-sidebar-content",children:[Object(m.jsx)("div",{className:"blog-search",children:Object(m.jsxs)(h.L,{className:"input-group-merge",children:[Object(m.jsx)(h.K,{placeholder:"Search here"}),Object(m.jsx)(h.M,{children:Object(m.jsx)(j.a,{size:14})})]})}),null!==c?Object(m.jsxs)(l.Fragment,{children:[Object(m.jsxs)("div",{className:"blog-recent-posts mt-3",children:[Object(m.jsx)("h6",{className:"section-label",children:"Recent Posts"}),Object(m.jsx)("div",{className:"mt-75",children:c.recentPosts.map((function(e,t){return Object(m.jsxs)("div",{className:d()("d-flex",{"mb-2":t!==c.recentPosts.length-1}),children:[Object(m.jsx)(n.b,{className:"me-2",to:"/pages/blog/detail/".concat(e.id),children:Object(m.jsx)("img",{className:"rounded",src:e.img,alt:e.title,width:"100",height:"70"})}),Object(m.jsxs)("div",{children:[Object(m.jsx)("h6",{className:"blog-recent-post-title",children:Object(m.jsx)(n.b,{className:"text-body-heading",to:"/pages/blog/detail/".concat(e.id),children:e.title})}),Object(m.jsx)("div",{className:"text-muted mb-0",children:e.createdTime})]})]},t)}))})]}),Object(m.jsxs)("div",{className:"blog-categories mt-3",children:[Object(m.jsx)("h6",{className:"section-label",children:"Categories"}),Object(m.jsx)("div",{className:"mt-1",children:c.categories.map((function(e,t){var s=b[e.icon];return Object(m.jsxs)("div",{className:d()("d-flex justify-content-start align-items-center",{"mb-75":t!==c.categories.length-1}),children:[Object(m.jsx)("a",{className:"me-75",href:"/",onClick:function(e){return e.preventDefault()},children:Object(m.jsx)(o.a,{className:"rounded",color:i[e.category],icon:Object(m.jsx)(s,{size:15})})}),Object(m.jsx)("a",{href:"/",onClick:function(e){return e.preventDefault()},children:Object(m.jsx)("div",{className:"blog-category-title text-body",children:e.category})})]},t)}))})]})]}):null]})})})})}}}]);
//# sourceMappingURL=74.cde1f5ea.chunk.js.map