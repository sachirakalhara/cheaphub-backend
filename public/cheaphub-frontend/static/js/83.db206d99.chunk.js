(this.webpackJsonpSeamless=this.webpackJsonpSeamless||[]).push([[83],{1428:function(e,c,s){},1680:function(e,c,s){"use strict";s.r(c);var t=s(1),a=s(541),i=s(972),r=s(990),n=s(1069),l=s(96),j=s(14),d=function(){return Object(j.jsx)("div",{className:"item-features",children:Object(j.jsxs)(l.ib,{className:"text-center",children:[Object(j.jsx)(l.B,{className:"mb-4 mb-md-0",md:"4",xs:"12",children:Object(j.jsxs)("div",{className:"w-75 mx-auto",children:[Object(j.jsx)(i.a,{}),Object(j.jsx)("h4",{className:"mt-2 mb-1",children:"100% Original"}),Object(j.jsx)(l.u,{children:"Chocolate bar candy canes ice cream toffee. Croissant pie cookie halvah."})]})}),Object(j.jsx)(l.B,{className:"mb-4 mb-md-0",md:"4",xs:"12",children:Object(j.jsxs)("div",{className:"w-75 mx-auto",children:[Object(j.jsx)(r.a,{}),Object(j.jsx)("h4",{className:"mt-2 mb-1",children:"10 Day Replacement"}),Object(j.jsx)(l.u,{children:"Marshmallow biscuit donut drag\xe9e fruitcake. Jujubes wafer cupcake."})]})}),Object(j.jsx)(l.B,{className:"mb-4 mb-md-0",md:"4",xs:"12",children:Object(j.jsxs)("div",{className:"w-75 mx-auto",children:[Object(j.jsx)(n.a,{}),Object(j.jsx)("h4",{className:"mt-2 mb-1",children:"1 Year Warranty"}),Object(j.jsx)(l.u,{children:"Cotton candy gingerbread cake I love sugar plum I love sweet croissant."})]})})]})})},m=s(10),b=s(20),o=s(488),h=s(2),x=s.n(h),u=s(1075),p=s(959),O=s(1005),f=s(1026),g=s(1067),N=s(1015),v=s(1086),w=s(1101),y=s(1032),C=function(e){var c=e.data,s=e.deleteWishlistItem,a=e.dispatch,i=e.addToWishlist,r=e.getProduct,n=e.productId,d=e.addToCart,h=Object(t.useState)("primary"),C=Object(b.a)(h,2),k=C[0],P=C[1],I=c.isInCart?o.b:"button";return Object(j.jsxs)(l.ib,{className:"my-2",children:[Object(j.jsx)(l.B,{className:"d-flex align-items-center justify-content-center mb-2 mb-md-0",md:"5",xs:"12",children:Object(j.jsx)("div",{className:"d-flex align-items-center justify-content-center",children:Object(j.jsx)("img",{className:"img-fluid product-img",src:c.image,alt:c.name})})}),Object(j.jsxs)(l.B,{md:"7",xs:"12",children:[Object(j.jsx)("h4",{children:c.name}),Object(j.jsxs)(l.u,{tag:"span",className:"item-company",children:["By",Object(j.jsx)("a",{className:"company-name",href:"/",onClick:function(e){return e.preventDefault()},children:c.brand})]}),Object(j.jsxs)("div",{className:"ecommerce-details-price d-flex flex-wrap mt-1",children:[Object(j.jsxs)("h4",{className:"item-price me-1",children:["$",c.price]}),Object(j.jsx)("ul",{className:"unstyled-list list-inline",children:new Array(5).fill().map((function(e,s){return Object(j.jsx)("li",{className:"ratings-list-item me-25",children:Object(j.jsx)(u.a,{className:x()({"filled-star":s+1<=c.rating,"unfilled-star":s+1>c.rating})})},s)}))})]}),Object(j.jsxs)(l.u,{children:["Available -",Object(j.jsx)("span",{className:"text-success ms-25",children:"In stock"})]}),Object(j.jsx)(l.u,{children:c.description}),Object(j.jsxs)("ul",{className:"product-features list-unstyled",children:[c.hasFreeShipping?Object(j.jsxs)("li",{children:[Object(j.jsx)(p.a,{size:19}),Object(j.jsx)("span",{children:"Free Shipping"})]}):null,Object(j.jsxs)("li",{children:[Object(j.jsx)(O.a,{size:19}),Object(j.jsx)("span",{children:"EMI options available"})]})]}),Object(j.jsx)("hr",{}),Object(j.jsxs)("div",{className:"product-color-options",children:[Object(j.jsx)("h6",{children:"Colors"}),Object(j.jsx)("ul",{className:"list-unstyled mb-0",children:c.colorOptions.map((function(e,s){var t=c.colorOptions.length-1===s;return Object(j.jsx)("li",{className:x()("d-inline-block",{"me-25":!t,selected:k===e}),onClick:function(){return P(e)},children:Object(j.jsx)("div",{className:"color-option b-".concat(e),children:Object(j.jsx)("div",{className:"filloption bg-".concat(e)})})},e)}))})]}),Object(j.jsx)("hr",{}),Object(j.jsxs)("div",{className:"d-flex flex-column flex-sm-row pt-1",children:[Object(j.jsxs)(l.i,Object(m.a)(Object(m.a)({tag:I,className:"btn-cart me-0 me-sm-1 mb-1 mb-sm-0",color:"primary",onClick:function(){return e=c.id,!1===c.isInCart&&a(d(e)),void a(r(n));var e}},c.isInCart?{to:"/apps/ecommerce/checkout"}:{}),{},{children:[Object(j.jsx)(p.a,{className:"me-50",size:14}),c.isInCart?"View in cart":"Move to cart"]})),Object(j.jsxs)(l.i,{className:"btn-wishlist me-0 me-sm-1 mb-1 mb-sm-0",color:"secondary",outline:!0,onClick:function(){return e=c.isInWishlist,a(e?s(n):i(n)),void a(r(n));var e},children:[Object(j.jsx)(f.a,{size:14,className:x()("me-50",{"text-danger":c.isInWishlist})}),Object(j.jsx)("span",{children:"Wishlist"})]}),Object(j.jsxs)(l.tb,{className:"dropdown-icon-wrapper btn-share",children:[Object(j.jsx)(l.G,{className:"btn-icon hide-arrow",color:"secondary",caret:!0,outline:!0,children:Object(j.jsx)(g.a,{size:14})}),Object(j.jsxs)(l.F,{end:!0,children:[Object(j.jsx)(l.E,{tag:"a",href:"/",onClick:function(e){return e.preventDefault()},children:Object(j.jsx)(N.a,{size:14})}),Object(j.jsx)(l.E,{tag:"a",href:"/",onClick:function(e){return e.preventDefault()},children:Object(j.jsx)(v.a,{size:14})}),Object(j.jsx)(l.E,{tag:"a",href:"/",onClick:function(e){return e.preventDefault()},children:Object(j.jsx)(w.a,{size:14})}),Object(j.jsx)(l.E,{tag:"a",href:"/",onClick:function(e){return e.preventDefault()},children:Object(j.jsx)(y.a,{size:14})})]})]})]})]})]})},k=s(774),P=s(537),I=s.p+"static/media/apple-watch.884c5ea7.png",A=s.p+"static/media/macbook-pro.4ecc26e8.png",B=s.p+"static/media/homepod.c5fa0cec.png",D=s.p+"static/media/magic-mouse.f8ccce24.png",z=s.p+"static/media/iphone-x.ba5bfe17.png",W=(s(1144),function(){k.k.use([k.g]);var e=[{name:"Apple Watch Series 6",brand:"Apple",ratings:4,price:399.98,img:I},{name:"Apple MacBook Pro - Silver",brand:"Apple",ratings:2,price:2449.49,img:A},{name:"Apple HomePod (Space Grey)",brand:"Apple",ratings:3,price:229.29,img:B},{name:"Magic Mouse 2 - Black",brand:"Apple",ratings:3,price:90.98,img:D},{name:"iPhone 12 Pro",brand:"Apple",ratings:4,price:1559.99,img:z}];return Object(j.jsxs)(t.Fragment,{children:[Object(j.jsxs)("div",{className:"mt-4 mb-2 text-center",children:[Object(j.jsx)("h4",{children:"Related Products"}),Object(j.jsx)(l.u,{children:"People also search for this items"})]}),Object(j.jsx)(P.a,Object(m.a)(Object(m.a)({},{className:"swiper-responsive-breakpoints swiper-container px-4 py-2",slidesPerView:5,spaceBetween:55,navigation:!0,breakpoints:{1600:{slidesPerView:4,spaceBetween:55},1300:{slidesPerView:3,spaceBetween:55},768:{slidesPerView:2,spaceBetween:55},320:{slidesPerView:1,spaceBetween:55}}}),{},{children:e.map((function(e){return Object(j.jsx)(P.b,{children:Object(j.jsxs)("a",{href:"/",onClick:function(e){return e.preventDefault()},children:[Object(j.jsxs)("div",{className:"item-heading",children:[Object(j.jsx)("h5",{className:"text-truncate mb-0",children:e.name}),Object(j.jsxs)("small",{className:"text-body",children:["by ",e.brand]})]}),Object(j.jsx)("div",{className:"img-container w-50 mx-auto py-75",children:Object(j.jsx)("img",{src:e.img,alt:"swiper 1",className:"img-fluid"})}),Object(j.jsxs)("div",{className:"item-meta",children:[Object(j.jsx)("ul",{className:"unstyled-list list-inline mb-25",children:new Array(5).fill().map((function(c,s){return Object(j.jsx)("li",{className:"ratings-list-item me-25",children:Object(j.jsx)(u.a,{className:x()({"filled-star":s+1<=e.ratings,"unfilled-star":s+1>e.ratings})})},s)}))}),Object(j.jsxs)(l.u,{className:"text-primary mb-0",children:["$",e.price]})]})]})},e.name)}))}))]})}),S=s(489),E=s(72),M=s(147);s(1428),c.default=function(){var e=Object(a.i)().product,c=e.substring(e.lastIndexOf("-")+1),s=Object(E.b)(),i=Object(E.c)((function(e){return e.ecommerce}));return Object(t.useEffect)((function(){s(Object(M.g)(c))}),[]),Object(j.jsxs)(t.Fragment,{children:[Object(j.jsx)(S.a,{breadCrumbTitle:"Product Details",breadCrumbParent:"eCommerce",breadCrumbActive:"Details"}),Object(j.jsx)("div",{className:"app-ecommerce-details",children:Object.keys(i.productDetail).length?Object(j.jsxs)(l.l,{children:[Object(j.jsx)(l.m,{children:Object(j.jsx)(C,{dispatch:s,addToCart:M.a,productId:c,getProduct:M.g,data:i.productDetail,addToWishlist:M.b,deleteWishlistItem:M.e})}),Object(j.jsx)(d,{}),Object(j.jsx)(l.m,{children:Object(j.jsx)(W,{})})]}):null})]})}},489:function(e,c,s){"use strict";var t=s(488),a=s(96),i=s(14);c.a=function(e){var c=e.breadCrumbTitle,s=e.breadCrumbParent,r=e.breadCrumbParent2,n=e.breadCrumbParent3,l=e.breadCrumbActive;return Object(i.jsx)("div",{className:"content-header row",children:Object(i.jsx)("div",{className:"content-header-left col-md-9 col-12 mb-2",children:Object(i.jsx)("div",{className:"row breadcrumbs-top",children:Object(i.jsxs)("div",{className:"col-12",children:[c?Object(i.jsx)("h2",{className:"content-header-title float-start mb-0",children:c}):"",Object(i.jsx)("div",{className:"breadcrumb-wrapper vs-breadcrumbs d-sm-block d-none col-12",children:Object(i.jsxs)(a.g,{children:[Object(i.jsx)(a.h,{tag:"li",children:Object(i.jsx)(t.b,{to:"/",children:"Home"})}),Object(i.jsx)(a.h,{tag:"li",className:"text-primary",children:s}),r?Object(i.jsx)(a.h,{tag:"li",className:"text-primary",children:r}):"",n?Object(i.jsx)(a.h,{tag:"li",className:"text-primary",children:n}):"",Object(i.jsx)(a.h,{tag:"li",active:!0,children:l})]})})]})})})})}}}]);
//# sourceMappingURL=83.db206d99.chunk.js.map