var TradeInCalculator=function($){"use strict";var ot=Object.defineProperty;var at=($,y,_)=>y in $?ot($,y,{enumerable:!0,configurable:!0,writable:!0,value:_}):$[y]=_;var F=($,y,_)=>at($,typeof y!="symbol"?y+"":y,_);/**
 * @license
 * Copyright 2019 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */var _e;const y=globalThis,_=y.ShadowRoot&&(y.ShadyCSS===void 0||y.ShadyCSS.nativeShadow)&&"adoptedStyleSheets"in Document.prototype&&"replace"in CSSStyleSheet.prototype,G=Symbol(),ee=new WeakMap;let te=class{constructor(e,t,i){if(this._$cssResult$=!0,i!==G)throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");this.cssText=e,this.t=t}get styleSheet(){let e=this.o;const t=this.t;if(_&&e===void 0){const i=t!==void 0&&t.length===1;i&&(e=ee.get(t)),e===void 0&&((this.o=e=new CSSStyleSheet).replaceSync(this.cssText),i&&ee.set(t,e))}return e}toString(){return this.cssText}};const Ae=r=>new te(typeof r=="string"?r:r+"",void 0,G),Se=(r,...e)=>{const t=r.length===1?r[0]:e.reduce((i,s,n)=>i+(o=>{if(o._$cssResult$===!0)return o.cssText;if(typeof o=="number")return o;throw Error("Value passed to 'css' function must be a 'css' function result: "+o+". Use 'unsafeCSS' to pass non-literal values, but take care to ensure page security.")})(s)+r[n+1],r[0]);return new te(t,r,G)},xe=(r,e)=>{if(_)r.adoptedStyleSheets=e.map(t=>t instanceof CSSStyleSheet?t:t.styleSheet);else for(const t of e){const i=document.createElement("style"),s=y.litNonce;s!==void 0&&i.setAttribute("nonce",s),i.textContent=t.cssText,r.appendChild(i)}},ie=_?r=>r:r=>r instanceof CSSStyleSheet?(e=>{let t="";for(const i of e.cssRules)t+=i.cssText;return Ae(t)})(r):r;/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const{is:we,defineProperty:Ce,getOwnPropertyDescriptor:ke,getOwnPropertyNames:Te,getOwnPropertySymbols:Pe,getPrototypeOf:Ie}=Object,E=globalThis,re=E.trustedTypes,Ne=re?re.emptyScript:"",j=E.reactiveElementPolyfillSupport,I=(r,e)=>r,W={toAttribute(r,e){switch(e){case Boolean:r=r?Ne:null;break;case Object:case Array:r=r==null?r:JSON.stringify(r)}return r},fromAttribute(r,e){let t=r;switch(e){case Boolean:t=r!==null;break;case Number:t=r===null?null:Number(r);break;case Object:case Array:try{t=JSON.parse(r)}catch{t=null}}return t}},se=(r,e)=>!we(r,e),ne={attribute:!0,type:String,converter:W,reflect:!1,useDefault:!1,hasChanged:se};Symbol.metadata??(Symbol.metadata=Symbol("metadata")),E.litPropertyMetadata??(E.litPropertyMetadata=new WeakMap);let k=class extends HTMLElement{static addInitializer(e){this._$Ei(),(this.l??(this.l=[])).push(e)}static get observedAttributes(){return this.finalize(),this._$Eh&&[...this._$Eh.keys()]}static createProperty(e,t=ne){if(t.state&&(t.attribute=!1),this._$Ei(),this.prototype.hasOwnProperty(e)&&((t=Object.create(t)).wrapped=!0),this.elementProperties.set(e,t),!t.noAccessor){const i=Symbol(),s=this.getPropertyDescriptor(e,i,t);s!==void 0&&Ce(this.prototype,e,s)}}static getPropertyDescriptor(e,t,i){const{get:s,set:n}=ke(this.prototype,e)??{get(){return this[t]},set(o){this[t]=o}};return{get:s,set(o){const a=s==null?void 0:s.call(this);n==null||n.call(this,o),this.requestUpdate(e,a,i)},configurable:!0,enumerable:!0}}static getPropertyOptions(e){return this.elementProperties.get(e)??ne}static _$Ei(){if(this.hasOwnProperty(I("elementProperties")))return;const e=Ie(this);e.finalize(),e.l!==void 0&&(this.l=[...e.l]),this.elementProperties=new Map(e.elementProperties)}static finalize(){if(this.hasOwnProperty(I("finalized")))return;if(this.finalized=!0,this._$Ei(),this.hasOwnProperty(I("properties"))){const t=this.properties,i=[...Te(t),...Pe(t)];for(const s of i)this.createProperty(s,t[s])}const e=this[Symbol.metadata];if(e!==null){const t=litPropertyMetadata.get(e);if(t!==void 0)for(const[i,s]of t)this.elementProperties.set(i,s)}this._$Eh=new Map;for(const[t,i]of this.elementProperties){const s=this._$Eu(t,i);s!==void 0&&this._$Eh.set(s,t)}this.elementStyles=this.finalizeStyles(this.styles)}static finalizeStyles(e){const t=[];if(Array.isArray(e)){const i=new Set(e.flat(1/0).reverse());for(const s of i)t.unshift(ie(s))}else e!==void 0&&t.push(ie(e));return t}static _$Eu(e,t){const i=t.attribute;return i===!1?void 0:typeof i=="string"?i:typeof e=="string"?e.toLowerCase():void 0}constructor(){super(),this._$Ep=void 0,this.isUpdatePending=!1,this.hasUpdated=!1,this._$Em=null,this._$Ev()}_$Ev(){var e;this._$ES=new Promise(t=>this.enableUpdating=t),this._$AL=new Map,this._$E_(),this.requestUpdate(),(e=this.constructor.l)==null||e.forEach(t=>t(this))}addController(e){var t;(this._$EO??(this._$EO=new Set)).add(e),this.renderRoot!==void 0&&this.isConnected&&((t=e.hostConnected)==null||t.call(e))}removeController(e){var t;(t=this._$EO)==null||t.delete(e)}_$E_(){const e=new Map,t=this.constructor.elementProperties;for(const i of t.keys())this.hasOwnProperty(i)&&(e.set(i,this[i]),delete this[i]);e.size>0&&(this._$Ep=e)}createRenderRoot(){const e=this.shadowRoot??this.attachShadow(this.constructor.shadowRootOptions);return xe(e,this.constructor.elementStyles),e}connectedCallback(){var e;this.renderRoot??(this.renderRoot=this.createRenderRoot()),this.enableUpdating(!0),(e=this._$EO)==null||e.forEach(t=>{var i;return(i=t.hostConnected)==null?void 0:i.call(t)})}enableUpdating(e){}disconnectedCallback(){var e;(e=this._$EO)==null||e.forEach(t=>{var i;return(i=t.hostDisconnected)==null?void 0:i.call(t)})}attributeChangedCallback(e,t,i){this._$AK(e,i)}_$ET(e,t){var n;const i=this.constructor.elementProperties.get(e),s=this.constructor._$Eu(e,i);if(s!==void 0&&i.reflect===!0){const o=(((n=i.converter)==null?void 0:n.toAttribute)!==void 0?i.converter:W).toAttribute(t,i.type);this._$Em=e,o==null?this.removeAttribute(s):this.setAttribute(s,o),this._$Em=null}}_$AK(e,t){var n,o;const i=this.constructor,s=i._$Eh.get(e);if(s!==void 0&&this._$Em!==s){const a=i.getPropertyOptions(s),l=typeof a.converter=="function"?{fromAttribute:a.converter}:((n=a.converter)==null?void 0:n.fromAttribute)!==void 0?a.converter:W;this._$Em=s;const c=l.fromAttribute(t,a.type);this[s]=c??((o=this._$Ej)==null?void 0:o.get(s))??c,this._$Em=null}}requestUpdate(e,t,i,s=!1,n){var o;if(e!==void 0){const a=this.constructor;if(s===!1&&(n=this[e]),i??(i=a.getPropertyOptions(e)),!((i.hasChanged??se)(n,t)||i.useDefault&&i.reflect&&n===((o=this._$Ej)==null?void 0:o.get(e))&&!this.hasAttribute(a._$Eu(e,i))))return;this.C(e,t,i)}this.isUpdatePending===!1&&(this._$ES=this._$EP())}C(e,t,{useDefault:i,reflect:s,wrapped:n},o){i&&!(this._$Ej??(this._$Ej=new Map)).has(e)&&(this._$Ej.set(e,o??t??this[e]),n!==!0||o!==void 0)||(this._$AL.has(e)||(this.hasUpdated||i||(t=void 0),this._$AL.set(e,t)),s===!0&&this._$Em!==e&&(this._$Eq??(this._$Eq=new Set)).add(e))}async _$EP(){this.isUpdatePending=!0;try{await this._$ES}catch(t){Promise.reject(t)}const e=this.scheduleUpdate();return e!=null&&await e,!this.isUpdatePending}scheduleUpdate(){return this.performUpdate()}performUpdate(){var i;if(!this.isUpdatePending)return;if(!this.hasUpdated){if(this.renderRoot??(this.renderRoot=this.createRenderRoot()),this._$Ep){for(const[n,o]of this._$Ep)this[n]=o;this._$Ep=void 0}const s=this.constructor.elementProperties;if(s.size>0)for(const[n,o]of s){const{wrapped:a}=o,l=this[n];a!==!0||this._$AL.has(n)||l===void 0||this.C(n,void 0,o,l)}}let e=!1;const t=this._$AL;try{e=this.shouldUpdate(t),e?(this.willUpdate(t),(i=this._$EO)==null||i.forEach(s=>{var n;return(n=s.hostUpdate)==null?void 0:n.call(s)}),this.update(t)):this._$EM()}catch(s){throw e=!1,this._$EM(),s}e&&this._$AE(t)}willUpdate(e){}_$AE(e){var t;(t=this._$EO)==null||t.forEach(i=>{var s;return(s=i.hostUpdated)==null?void 0:s.call(i)}),this.hasUpdated||(this.hasUpdated=!0,this.firstUpdated(e)),this.updated(e)}_$EM(){this._$AL=new Map,this.isUpdatePending=!1}get updateComplete(){return this.getUpdateComplete()}getUpdateComplete(){return this._$ES}shouldUpdate(e){return!0}update(e){this._$Eq&&(this._$Eq=this._$Eq.forEach(t=>this._$ET(t,this[t]))),this._$EM()}updated(e){}firstUpdated(e){}};k.elementStyles=[],k.shadowRootOptions={mode:"open"},k[I("elementProperties")]=new Map,k[I("finalized")]=new Map,j==null||j({ReactiveElement:k}),(E.reactiveElementVersions??(E.reactiveElementVersions=[])).push("2.1.2");/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const N=globalThis,oe=r=>r,M=N.trustedTypes,ae=M?M.createPolicy("lit-html",{createHTML:r=>r}):void 0,le="$lit$",A=`lit$${Math.random().toFixed(9).slice(2)}$`,ce="?"+A,Re=`<${ce}>`,S=document,R=()=>S.createComment(""),O=r=>r===null||typeof r!="object"&&typeof r!="function",Q=Array.isArray,Oe=r=>Q(r)||typeof(r==null?void 0:r[Symbol.iterator])=="function",Y=`[ 	
\f\r]`,D=/<(?:(!--|\/[^a-zA-Z])|(\/?[a-zA-Z][^>\s]*)|(\/?$))/g,de=/-->/g,ue=/>/g,x=RegExp(`>|${Y}(?:([^\\s"'>=/]+)(${Y}*=${Y}*(?:[^ 	
\f\r"'\`<>=]|("|')|))|$)`,"g"),pe=/'/g,he=/"/g,me=/^(?:script|style|textarea|title)$/i,De=r=>(e,...t)=>({_$litType$:r,strings:e,values:t}),p=De(1),T=Symbol.for("lit-noChange"),b=Symbol.for("lit-nothing"),be=new WeakMap,w=S.createTreeWalker(S,129);function fe(r,e){if(!Q(r)||!r.hasOwnProperty("raw"))throw Error("invalid template strings array");return ae!==void 0?ae.createHTML(e):e}const Le=(r,e)=>{const t=r.length-1,i=[];let s,n=e===2?"<svg>":e===3?"<math>":"",o=D;for(let a=0;a<t;a++){const l=r[a];let c,h,d=-1,m=0;for(;m<l.length&&(o.lastIndex=m,h=o.exec(l),h!==null);)m=o.lastIndex,o===D?h[1]==="!--"?o=de:h[1]!==void 0?o=ue:h[2]!==void 0?(me.test(h[2])&&(s=RegExp("</"+h[2],"g")),o=x):h[3]!==void 0&&(o=x):o===x?h[0]===">"?(o=s??D,d=-1):h[1]===void 0?d=-2:(d=o.lastIndex-h[2].length,c=h[1],o=h[3]===void 0?x:h[3]==='"'?he:pe):o===he||o===pe?o=x:o===de||o===ue?o=D:(o=x,s=void 0);const u=o===x&&r[a+1].startsWith("/>")?" ":"";n+=o===D?l+Re:d>=0?(i.push(c),l.slice(0,d)+le+l.slice(d)+A+u):l+A+(d===-2?a:u)}return[fe(r,n+(r[t]||"<?>")+(e===2?"</svg>":e===3?"</math>":"")),i]};class L{constructor({strings:e,_$litType$:t},i){let s;this.parts=[];let n=0,o=0;const a=e.length-1,l=this.parts,[c,h]=Le(e,t);if(this.el=L.createElement(c,i),w.currentNode=this.el.content,t===2||t===3){const d=this.el.content.firstChild;d.replaceWith(...d.childNodes)}for(;(s=w.nextNode())!==null&&l.length<a;){if(s.nodeType===1){if(s.hasAttributes())for(const d of s.getAttributeNames())if(d.endsWith(le)){const m=h[o++],u=s.getAttribute(d).split(A),g=/([.?@])?(.*)/.exec(m);l.push({type:1,index:n,name:g[2],strings:u,ctor:g[1]==="."?Ue:g[1]==="?"?Be:g[1]==="@"?Me:H}),s.removeAttribute(d)}else d.startsWith(A)&&(l.push({type:6,index:n}),s.removeAttribute(d));if(me.test(s.tagName)){const d=s.textContent.split(A),m=d.length-1;if(m>0){s.textContent=M?M.emptyScript:"";for(let u=0;u<m;u++)s.append(d[u],R()),w.nextNode(),l.push({type:2,index:++n});s.append(d[m],R())}}}else if(s.nodeType===8)if(s.data===ce)l.push({type:2,index:n});else{let d=-1;for(;(d=s.data.indexOf(A,d+1))!==-1;)l.push({type:7,index:n}),d+=A.length-1}n++}}static createElement(e,t){const i=S.createElement("template");return i.innerHTML=e,i}}function P(r,e,t=r,i){var o,a;if(e===T)return e;let s=i!==void 0?(o=t._$Co)==null?void 0:o[i]:t._$Cl;const n=O(e)?void 0:e._$litDirective$;return(s==null?void 0:s.constructor)!==n&&((a=s==null?void 0:s._$AO)==null||a.call(s,!1),n===void 0?s=void 0:(s=new n(r),s._$AT(r,t,i)),i!==void 0?(t._$Co??(t._$Co=[]))[i]=s:t._$Cl=s),s!==void 0&&(e=P(r,s._$AS(r,e.values),s,i)),e}class qe{constructor(e,t){this._$AV=[],this._$AN=void 0,this._$AD=e,this._$AM=t}get parentNode(){return this._$AM.parentNode}get _$AU(){return this._$AM._$AU}u(e){const{el:{content:t},parts:i}=this._$AD,s=((e==null?void 0:e.creationScope)??S).importNode(t,!0);w.currentNode=s;let n=w.nextNode(),o=0,a=0,l=i[0];for(;l!==void 0;){if(o===l.index){let c;l.type===2?c=new q(n,n.nextSibling,this,e):l.type===1?c=new l.ctor(n,l.name,l.strings,this,e):l.type===6&&(c=new He(n,this,e)),this._$AV.push(c),l=i[++a]}o!==(l==null?void 0:l.index)&&(n=w.nextNode(),o++)}return w.currentNode=S,s}p(e){let t=0;for(const i of this._$AV)i!==void 0&&(i.strings!==void 0?(i._$AI(e,i,t),t+=i.strings.length-2):i._$AI(e[t])),t++}}class q{get _$AU(){var e;return((e=this._$AM)==null?void 0:e._$AU)??this._$Cv}constructor(e,t,i,s){this.type=2,this._$AH=b,this._$AN=void 0,this._$AA=e,this._$AB=t,this._$AM=i,this.options=s,this._$Cv=(s==null?void 0:s.isConnected)??!0}get parentNode(){let e=this._$AA.parentNode;const t=this._$AM;return t!==void 0&&(e==null?void 0:e.nodeType)===11&&(e=t.parentNode),e}get startNode(){return this._$AA}get endNode(){return this._$AB}_$AI(e,t=this){e=P(this,e,t),O(e)?e===b||e==null||e===""?(this._$AH!==b&&this._$AR(),this._$AH=b):e!==this._$AH&&e!==T&&this._(e):e._$litType$!==void 0?this.$(e):e.nodeType!==void 0?this.T(e):Oe(e)?this.k(e):this._(e)}O(e){return this._$AA.parentNode.insertBefore(e,this._$AB)}T(e){this._$AH!==e&&(this._$AR(),this._$AH=this.O(e))}_(e){this._$AH!==b&&O(this._$AH)?this._$AA.nextSibling.data=e:this.T(S.createTextNode(e)),this._$AH=e}$(e){var n;const{values:t,_$litType$:i}=e,s=typeof i=="number"?this._$AC(e):(i.el===void 0&&(i.el=L.createElement(fe(i.h,i.h[0]),this.options)),i);if(((n=this._$AH)==null?void 0:n._$AD)===s)this._$AH.p(t);else{const o=new qe(s,this),a=o.u(this.options);o.p(t),this.T(a),this._$AH=o}}_$AC(e){let t=be.get(e.strings);return t===void 0&&be.set(e.strings,t=new L(e)),t}k(e){Q(this._$AH)||(this._$AH=[],this._$AR());const t=this._$AH;let i,s=0;for(const n of e)s===t.length?t.push(i=new q(this.O(R()),this.O(R()),this,this.options)):i=t[s],i._$AI(n),s++;s<t.length&&(this._$AR(i&&i._$AB.nextSibling,s),t.length=s)}_$AR(e=this._$AA.nextSibling,t){var i;for((i=this._$AP)==null?void 0:i.call(this,!1,!0,t);e!==this._$AB;){const s=oe(e).nextSibling;oe(e).remove(),e=s}}setConnected(e){var t;this._$AM===void 0&&(this._$Cv=e,(t=this._$AP)==null||t.call(this,e))}}class H{get tagName(){return this.element.tagName}get _$AU(){return this._$AM._$AU}constructor(e,t,i,s,n){this.type=1,this._$AH=b,this._$AN=void 0,this.element=e,this.name=t,this._$AM=s,this.options=n,i.length>2||i[0]!==""||i[1]!==""?(this._$AH=Array(i.length-1).fill(new String),this.strings=i):this._$AH=b}_$AI(e,t=this,i,s){const n=this.strings;let o=!1;if(n===void 0)e=P(this,e,t,0),o=!O(e)||e!==this._$AH&&e!==T,o&&(this._$AH=e);else{const a=e;let l,c;for(e=n[0],l=0;l<n.length-1;l++)c=P(this,a[i+l],t,l),c===T&&(c=this._$AH[l]),o||(o=!O(c)||c!==this._$AH[l]),c===b?e=b:e!==b&&(e+=(c??"")+n[l+1]),this._$AH[l]=c}o&&!s&&this.j(e)}j(e){e===b?this.element.removeAttribute(this.name):this.element.setAttribute(this.name,e??"")}}class Ue extends H{constructor(){super(...arguments),this.type=3}j(e){this.element[this.name]=e===b?void 0:e}}class Be extends H{constructor(){super(...arguments),this.type=4}j(e){this.element.toggleAttribute(this.name,!!e&&e!==b)}}class Me extends H{constructor(e,t,i,s,n){super(e,t,i,s,n),this.type=5}_$AI(e,t=this){if((e=P(this,e,t,0)??b)===T)return;const i=this._$AH,s=e===b&&i!==b||e.capture!==i.capture||e.once!==i.once||e.passive!==i.passive,n=e!==b&&(i===b||s);s&&this.element.removeEventListener(this.name,this,i),n&&this.element.addEventListener(this.name,this,e),this._$AH=e}handleEvent(e){var t;typeof this._$AH=="function"?this._$AH.call(((t=this.options)==null?void 0:t.host)??this.element,e):this._$AH.handleEvent(e)}}class He{constructor(e,t,i){this.element=e,this.type=6,this._$AN=void 0,this._$AM=t,this.options=i}get _$AU(){return this._$AM._$AU}_$AI(e){P(this,e)}}const K=N.litHtmlPolyfillSupport;K==null||K(L,q),(N.litHtmlVersions??(N.litHtmlVersions=[])).push("3.3.3");const Ve=(r,e,t)=>{const i=(t==null?void 0:t.renderBefore)??e;let s=i._$litPart$;if(s===void 0){const n=(t==null?void 0:t.renderBefore)??null;i._$litPart$=s=new q(e.insertBefore(R(),n),n,void 0,t??{})}return s._$AI(r),s};/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const C=globalThis;class U extends k{constructor(){super(...arguments),this.renderOptions={host:this},this._$Do=void 0}createRenderRoot(){var t;const e=super.createRenderRoot();return(t=this.renderOptions).renderBefore??(t.renderBefore=e.firstChild),e}update(e){const t=this.render();this.hasUpdated||(this.renderOptions.isConnected=this.isConnected),super.update(e),this._$Do=Ve(t,this.renderRoot,this.renderOptions)}connectedCallback(){var e;super.connectedCallback(),(e=this._$Do)==null||e.setConnected(!0)}disconnectedCallback(){var e;super.disconnectedCallback(),(e=this._$Do)==null||e.setConnected(!1)}render(){return T}}U._$litElement$=!0,U.finalized=!0,(_e=C.litElementHydrateSupport)==null||_e.call(C,{LitElement:U});const X=C.litElementPolyfillSupport;X==null||X({LitElement:U}),(C.litElementVersions??(C.litElementVersions=[])).push("4.2.2");const ze=Se`
    :host {
        display: block;
        width: 100%;
        box-sizing: border-box;
        font-family: 'IBM Plex Sans Arabic', sans-serif;
        --ti-primary: #2563eb;
        --ti-bg: #ffffff;
        --ti-text: #111827;
        --ti-radius: 14px;
        --ti-muted: #6b7280;
        --ti-border: rgba(17, 24, 39, 0.1);
        --ti-surface: color-mix(in srgb, var(--ti-primary) 6%, var(--ti-bg));
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    .ti-card {
        font-family: inherit;
        background: var(--ti-bg);
        color: var(--ti-text);
        border-radius: var(--ti-radius);
        border: 1px solid var(--ti-border);
        padding: 1.5rem;
        max-width: 480px;
        margin: 0 auto;
        box-shadow: 0 8px 24px rgba(17, 24, 39, 0.08);
        animation: ti-fade-in 0.2s ease;
    }

    .ti-card[dir='rtl'] {
        text-align: right;
    }

    .ti-card[dir='rtl'] .ti-checkbox-group {
        margin-left: 0;
        margin-right: 1.6rem;
    }

    .ti-card button,
    .ti-card input,
    .ti-card select,
    .ti-card textarea {
        font-family: inherit;
    }

    @keyframes ti-fade-in {
        from {
            opacity: 0;
            transform: translateY(4px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .ti-eyebrow {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--ti-muted);
        margin-bottom: 0.75rem;
    }

    .ti-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0 0 0.25rem;
        letter-spacing: -0.01em;
    }

    .ti-subtitle {
        color: var(--ti-muted);
        font-size: 0.9rem;
        margin: 0 0 1.25rem;
    }

    .ti-progress-track {
        display: flex;
        gap: 4px;
        margin-bottom: 1.25rem;
    }

    .ti-progress-dot {
        flex: 1;
        height: 5px;
        border-radius: 999px;
        background: var(--ti-border);
        transition: background 0.2s ease;
    }

    .ti-progress-dot.is-active {
        background: var(--ti-primary);
    }

    .ti-field {
        margin-bottom: 1.1rem;
    }

    .ti-label {
        display: block;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 0.4rem;
    }

    .ti-select,
    .ti-input {
        width: 100%;
        padding: 0.7rem 0.85rem;
        border-radius: calc(var(--ti-radius) - 6px);
        border: 1.5px solid var(--ti-border);
        font-size: 0.95rem;
        background: var(--ti-bg);
        color: var(--ti-text);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .ti-select {
        padding-right: 2.5rem;
    }

    .ti-card[dir='rtl'] .ti-select {
        padding-right: 0.85rem;
        padding-left: 2.5rem;
    }

    .ti-select:focus-visible,
    .ti-input:focus-visible {
        outline: none;
        border-color: var(--ti-primary);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--ti-primary) 20%, transparent);
    }

    .ti-option {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.75rem 0.9rem;
        border-radius: calc(var(--ti-radius) - 4px);
        border: 1.5px solid var(--ti-border);
        margin-bottom: 0.6rem;
        cursor: pointer;
        font-size: 0.92rem;
        transition: border-color 0.15s ease, background 0.15s ease, transform 0.1s ease;
    }

    .ti-option:hover {
        border-color: color-mix(in srgb, var(--ti-primary) 45%, var(--ti-border));
    }

    .ti-option:active {
        transform: scale(0.99);
    }

    .ti-option.is-active {
        border-color: var(--ti-primary);
        background: var(--ti-surface);
        font-weight: 600;
    }

    .ti-option input[type='radio'] {
        accent-color: var(--ti-primary);
        width: 1.05rem;
        height: 1.05rem;
        flex-shrink: 0;
    }

    .ti-checkbox-group {
        margin: 0.4rem 0 1.1rem 1.6rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .ti-checkbox-group label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        cursor: pointer;
    }

    .ti-checkbox-group input[type='checkbox'] {
        accent-color: var(--ti-primary);
        width: 1rem;
        height: 1rem;
    }

    .ti-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-top: 1.5rem;
    }

    .ti-btn {
        padding: 0.7rem 1.2rem;
        border-radius: calc(var(--ti-radius) - 4px);
        border: none;
        font-size: 0.92rem;
        font-weight: 600;
        cursor: pointer;
        transition: filter 0.15s ease, transform 0.1s ease, background 0.15s ease;
    }

    .ti-btn:active {
        transform: scale(0.98);
    }

    .ti-btn.is-primary {
        background: var(--ti-primary);
        color: #fff;
        flex: 1;
    }

    .ti-btn.is-primary:hover:not(:disabled) {
        filter: brightness(0.92);
    }

    .ti-btn.is-secondary {
        background: transparent;
        color: var(--ti-muted);
        border: 1.5px solid var(--ti-border);
    }

    .ti-btn.is-secondary:hover:not(:disabled) {
        background: var(--ti-surface);
        color: var(--ti-text);
    }

    .ti-btn.is-ghost {
        background: transparent;
        color: var(--ti-muted);
        padding: 0.7rem 0.5rem;
    }

    .ti-btn.is-ghost:hover:not(:disabled) {
        color: var(--ti-text);
    }

    .ti-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .ti-error {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #b91c1c;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: calc(var(--ti-radius) - 6px);
        padding: 0.6rem 0.8rem;
        font-size: 0.85rem;
        margin-bottom: 0.9rem;
    }

    .ti-result {
        text-align: center;
    }

    .ti-result-value {
        font-size: 2.25rem;
        font-weight: 800;
        color: var(--ti-primary);
        margin: 0.4rem 0 0.25rem;
        letter-spacing: -0.02em;
    }

    .ti-result-breakdown {
        color: var(--ti-muted);
        font-size: 0.85rem;
        margin: 0 0 1.25rem;
    }

    .ti-spinner {
        width: 1.1rem;
        height: 1.1rem;
        border: 2px solid rgba(255, 255, 255, 0.4);
        border-top-color: #fff;
        border-radius: 50%;
        display: inline-block;
        vertical-align: -2px;
        margin-right: 0.4rem;
        animation: ti-spin 0.7s linear infinite;
    }

    @keyframes ti-spin {
        to {
            transform: rotate(360deg);
        }
    }

    .ti-part-card {
        border: 1.5px solid var(--ti-border);
        border-radius: calc(var(--ti-radius) - 4px);
        padding: 0.85rem 1rem;
        margin-bottom: 0.75rem;
    }

    .ti-part-checkbox {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.55rem;
        font-size: 0.85rem;
        color: var(--ti-muted);
    }

    @media (max-width: 480px) {
        .ti-card {
            padding: 1.15rem;
            border-radius: 0;
            box-shadow: none;
            border-left: none;
            border-right: none;
        }
    }
`;class ge extends Error{constructor(e,t,i=null){super(e),this.status=t,this.code=i}}class Fe{constructor(e,t){this.apiBase=e.replace(/\/$/,""),this.clientId=t}async request(e,t={}){const i=await fetch(`${this.apiBase}${e}`,{...t,headers:{Accept:"application/json","X-Client-Id":this.clientId,...t.body?{"Content-Type":"application/json"}:{},...t.headers}});if(i.status===429)throw new ge("Too many requests, please try again shortly.",429);const s=await i.json().catch(()=>null);if(!i.ok)throw new ge((s==null?void 0:s.message)??`Request failed (${i.status})`,i.status,(s==null?void 0:s.code)??null);return s==null?void 0:s.data}getProducts(){return this.request("/products")}getVariants(e){return this.request(`/products/${e}/variants`)}getParts(e){return this.request(`/parts?productId=${encodeURIComponent(e)}`)}getQuestionnaire(){return this.request("/questionnaires/trade-in")}calculate(e){return this.request("/calculate",{method:"POST",body:JSON.stringify(e)})}}const f=Object.freeze({DEVICE_SELECT:"DEVICE_SELECT",QUESTIONNAIRE:"QUESTIONNAIRE",PARTS_SELECT:"PARTS_SELECT",CONTACT:"CONTACT",RESULT:"RESULT",NOT_ELIGIBLE:"NOT_ELIGIBLE"});function ye(){return{step:f.DEVICE_SELECT,loading:!1,error:null,products:[],variants:[],selectedProductId:null,selectedVariantId:null,questionnaire:null,questionnaireStepIndex:0,answers:{},needsPartsSelection:!1,parts:[],partsWithValuesMap:{},repairedPartsBefore:[],customer:{name:"",email:"",phoneNumber:""},result:null,history:[]}}function Ge(r){var e,t;return((t=(e=r.questionnaire)==null?void 0:e.steps)==null?void 0:t[r.questionnaireStepIndex])??null}function J(r){return[...r.history,{step:r.step,questionnaireStepIndex:r.questionnaireStepIndex}]}function je(r,e){var t;switch(e.type){case"LOADING":return{...r,loading:e.value,error:null};case"ERROR":return{...r,loading:!1,error:e.message};case"PRODUCTS_LOADED":return{...r,products:e.products};case"QUESTIONNAIRE_LOADED":return{...r,questionnaire:e.questionnaire};case"PARTS_LOADED":return{...r,parts:e.parts};case"SELECT_PRODUCT":return{...r,selectedProductId:e.productId,selectedVariantId:null,variants:[]};case"VARIANTS_LOADED":return{...r,variants:e.variants};case"SELECT_VARIANT":return{...r,selectedVariantId:e.variantId};case"GO_TO_QUESTIONNAIRE":return{...r,history:J(r),step:f.QUESTIONNAIRE,questionnaireStepIndex:0};case"SET_ANSWER":return{...r,answers:{...r.answers,[e.key]:{value:e.value,checkboxes:[]}}};case"TOGGLE_CHECKBOX":{const i=r.answers[e.key]??{value:null,checkboxes:[]},s=e.checked?[...i.checkboxes,e.value]:i.checkboxes.filter(n=>n!==e.value);return{...r,answers:{...r.answers,[e.key]:{...i,checkboxes:s}}}}case"QUESTIONNAIRE_NEXT":{const i=Ge(r),s=r.answers[i.key];if(!s)return r;const n=i.options.find(c=>c.value===s.value),o=r.questionnaire.steps,a=r.questionnaireStepIndex>=o.length-1,l=J(r);return n!=null&&n.hard_stop?{...r,history:l,step:f.NOT_ELIGIBLE}:n!=null&&n.triggers_parts_selection?{...r,history:l,needsPartsSelection:!0,step:f.PARTS_SELECT,questionnaireStepIndex:a?o.length:r.questionnaireStepIndex+1}:a?{...r,history:l,step:f.CONTACT}:{...r,history:l,questionnaireStepIndex:r.questionnaireStepIndex+1}}case"PARTS_DONE":{const i=((t=r.questionnaire)==null?void 0:t.steps)??[],s=r.questionnaireStepIndex,n=J(r);return s>=i.length?{...r,history:n,step:f.CONTACT}:{...r,history:n,step:f.QUESTIONNAIRE,questionnaireStepIndex:s}}case"SET_PART_VALUE":return{...r,partsWithValuesMap:{...r.partsWithValuesMap,[e.partId]:e.value}};case"SET_REPAIRED_PARTS_BEFORE":return{...r,repairedPartsBefore:e.partIds};case"BACK":{if(r.history.length===0)return r;const i=r.history[r.history.length-1];return{...r,step:i.step,questionnaireStepIndex:i.questionnaireStepIndex,history:r.history.slice(0,-1)}}case"UPDATE_CUSTOMER":return{...r,customer:{...r.customer,...e.customer}};case"RESULT_LOADED":return{...r,step:f.RESULT,result:e.result};case"RESTART":return ye();default:return r}}const We={"90+":98,"85-89":86,"80-84":81,below80:79};function v(r,e){var t;return(t=r.answers[e])==null?void 0:t.value}function V(r,e){var t;return((t=r.answers[e])==null?void 0:t.checkboxes)??[]}function Qe(r,e){const t=v(e,"power"),i=v(e,"repair_history"),s=v(e,"battery_health"),n=v(e,"screen_body"),o=v(e,"cosmetics"),a=v(e,"hardware"),l=v(e,"cameras"),c=v(e,"connectivity"),h=v(e,"biometrics"),d=V(e,"screen_body"),m=V(e,"hardware"),u=V(e,"cameras"),g=V(e,"connectivity"),Ee=o==="none"||!o?3:o==="light"?2:1;switch(r.type===null||r.type===void 0?null:Number(r.type)){case 0:return t==="yes"?1:0;case 1:return i==="local"?1:0;case 2:return We[s]??98;case 3:return n==="no"&&d.includes("Front Screen")?0:Ee;case 4:return n==="no"&&d.includes("Back Glass")?0:Ee;case 5:return a==="no"&&m.includes("Charging Port")?0:1;case 6:return a==="no"&&m.includes("Volume Buttons")?0:1;case 7:return a==="no"&&m.includes("Earpiece")?0:1;case 8:return a==="no"&&m.includes("Loudspeaker")?0:1;case 12:return l==="no"&&u.includes("Back Camera")?0:1;case 13:return l==="no"&&u.includes("Front Camera")?0:1;case 15:return a==="no"&&m.includes("Power Button")?0:1;case 17:return a==="no"&&m.includes("Vibration Motor")?0:1;case 20:case 21:return h==="yes"?1:0;case 23:return c==="no"&&g.includes("NFC")?0:1;case 24:return c==="no"&&g.includes("WiFi")?0:1;case 25:return c==="no"&&g.includes("Bluetooth")?0:1;default:return 1}}function $e(r){const e={},t=new Set(r.repairedPartsBefore.map(Number));for(const i of r.parts){const s=t.has(Number(i.id))?0:Qe(i,r);e[String(i.id)]=s}return{variantId:Number(r.selectedVariantId),customer:{name:r.customer.name,email:r.customer.email,phoneNumber:r.customer.phoneNumber},repairedPartsBefore:[...t],partsWithValuesMap:e}}function Ye(r,e,t){return p`
        <p class="ti-eyebrow"><span>${t("trade-in")}</span></p>
        <h2 class="ti-title">${t("select-device")}</h2>
        <p class="ti-subtitle">${t("choose-device")}</p>
        ${r.error?p`<p class="ti-error">⚠ ${r.error}</p>`:null}

        <div class="ti-field">
            <label class="ti-label" for="ti-model">${t("model")}</label>
            <select
                id="ti-model"
                class="ti-select"
                .value=${r.selectedProductId??""}
                @change=${i=>e({type:"SELECT_PRODUCT",productId:i.target.value})}
            >
                <option value="" disabled selected>${t("choose-model")}</option>
                ${r.products.map(i=>p`<option value=${i.id}>${i.brand_name?`${i.brand_name} `:""}${i.name}</option>`)}
            </select>
        </div>

        ${r.selectedProductId?p`
                  <div class="ti-field">
                          <label class="ti-label" for="ti-variant">${t("variant")}</label>
                      <select
                          id="ti-variant"
                          class="ti-select"
                          .value=${r.selectedVariantId??""}
                          @change=${i=>e({type:"SELECT_VARIANT",variantId:i.target.value})}
                      >
                          <option value="" disabled selected>${t("choose-variant")}</option>
                          ${r.variants.map(i=>p`<option value=${i.id}>${i.name}</option>`)}
                      </select>
                  </div>
              `:null}

        <div class="ti-actions">
            <span></span>
            <button
                class="ti-btn is-primary"
                ?disabled=${!r.selectedVariantId}
                @click=${()=>e({type:"GO_TO_QUESTIONNAIRE"})}
            >
                ${t("continue")}
            </button>
        </div>
    `}function Ke(r,e,t,i){var m;const s=((m=r.questionnaire)==null?void 0:m.steps)??[],n=s[r.questionnaireStepIndex];if(!n)return p`<p>${t("loading-questionnaire")}</p>`;const o=i(n),a=r.answers[n.key],l=o.options.find(u=>u.value===(a==null?void 0:a.value)),c=(l==null?void 0:l.conditional_checkboxes)??[];function h(u){e({type:"SET_ANSWER",key:n.key,value:u.value})}function d(u,g){e({type:"TOGGLE_CHECKBOX",key:n.key,value:u,checked:g})}return p`
        <div class="ti-progress-track">
            ${s.map((u,g)=>p`<div class="ti-progress-dot ${g<=r.questionnaireStepIndex?"is-active":""}"></div>`)}
        </div>
        <p class="ti-eyebrow">
            <span>${o.title}</span>
            <span>${t("step")} ${r.questionnaireStepIndex+1} ${t("of")} ${s.length}</span>
        </p>

        <h2 class="ti-title">${o.question}</h2>
        ${r.error?p`<p class="ti-error">⚠ ${r.error}</p>`:null}

        <div style="margin-top:1rem;">
            ${o.options.map(u=>p`
                    <label class="ti-option ${(a==null?void 0:a.value)===u.value?"is-active":""}">
                        <input
                            type="radio"
                            name=${n.key}
                            .checked=${(a==null?void 0:a.value)===u.value}
                            @change=${()=>h(u)}
                        />
                        ${u.label}
                    </label>
                `)}
        </div>
        ${c.length?p`
                  <div class="ti-checkbox-group">
                      ${c.map(u=>p`
                              <label>
                                  <input
                                      type="checkbox"
                                      .checked=${((a==null?void 0:a.checkboxes)??[]).includes(u.value)}
                                      @change=${g=>d(u.value,g.target.checked)}
                                  />
                                  ${u.label}
                              </label>
                          `)}
                  </div>
              `:null}

        <div class="ti-actions">
            <button class="ti-btn is-ghost" @click=${()=>e({type:"BACK"})}>← ${t("back")}</button>
            <button
                class="ti-btn is-primary"
                ?disabled=${!a}
                @click=${()=>e({type:"QUESTIONNAIRE_NEXT"})}
            >
                ${t("next")}
            </button>
        </div>
    `}function Xe(r,e,t){const i=r.parts.filter(n=>String(n.product_id)===String(r.selectedProductId)&&![0,1,2].includes(Number(n.type)));function s(n,o){const a=o?[...r.repairedPartsBefore,n]:r.repairedPartsBefore.filter(l=>l!==n);e({type:"SET_REPAIRED_PARTS_BEFORE",partIds:a})}return p`
        <p class="ti-eyebrow"><span>${t("repair-details")}</span></p>
        <h2 class="ti-title">${t("repaired-parts")}</h2>
        <p class="ti-subtitle">${t("repair-subtitle")}</p>
        ${r.error?p`<p class="ti-error">⚠ ${r.error}</p>`:null}

        ${i.length===0?p`<p class="ti-subtitle">${t("no-parts")}</p>`:null}

        ${i.map(n=>p`
                <div class="ti-part-card">
                    <label class="ti-label">${n.name}</label>
                    <label class="ti-part-checkbox">
                        <input
                            type="checkbox"
                            .checked=${r.repairedPartsBefore.includes(n.id)}
                            @change=${o=>s(n.id,o.target.checked)}
                        />
                        ${t("replaced-part")}
                    </label>
                </div>
            `)}

        <div class="ti-actions">
            <button class="ti-btn is-ghost" @click=${()=>e({type:"BACK"})}>← ${t("back")}</button>
            <button class="ti-btn is-primary" @click=${()=>e({type:"PARTS_DONE"})}>${t("continue")}</button>
        </div>
    `}const Je=/^[^\s@]+@[^\s@]+\.[^\s@]+$/,Ze=/^[0-9]{7,15}$/;function et(r){var e;return!!((e=r.name)!=null&&e.trim())&&Je.test(r.email??"")&&Ze.test(r.phoneNumber??"")}function tt(r,e,t,i){const{customer:s}=r;return p`
        <p class="ti-eyebrow"><span>${i("almost-done")}</span></p>
        <h2 class="ti-title">${i("details")}</h2>
        <p class="ti-subtitle">${i("contact-subtitle")}</p>
        ${r.error?p`<p class="ti-error">⚠ ${r.error}</p>`:null}

        <div class="ti-field">
            <label class="ti-label" for="ti-name">${i("full-name")}</label>
            <input
                id="ti-name"
                class="ti-input"
                type="text"
                .value=${s.name}
                @input=${n=>e({type:"UPDATE_CUSTOMER",customer:{name:n.target.value}})}
            />
        </div>

        <div class="ti-field">
            <label class="ti-label" for="ti-email">${i("email")}</label>
            <input
                id="ti-email"
                class="ti-input"
                type="email"
                .value=${s.email}
                @input=${n=>e({type:"UPDATE_CUSTOMER",customer:{email:n.target.value}})}
            />
        </div>

        <div class="ti-field">
            <label class="ti-label" for="ti-phone">${i("phone-number")}</label>
            <input
                id="ti-phone"
                class="ti-input"
                type="tel"
                .value=${s.phoneNumber}
                @input=${n=>e({type:"UPDATE_CUSTOMER",customer:{phoneNumber:n.target.value}})}
            />
        </div>

        <div class="ti-actions">
            <button class="ti-btn is-ghost" ?disabled=${r.loading} @click=${()=>e({type:"BACK"})}>← ${i("back")}</button>
            <button
                class="ti-btn is-primary"
                ?disabled=${r.loading||!et(s)}
                @click=${t}
            >
                ${r.loading?p`<span class="ti-spinner"></span>${i("calculating")}`:i("get-quote")}
            </button>
        </div>
    `}function it(r,e,t,i){const{result:s}=r;return p`
        <div class="ti-result">
            <p class="ti-eyebrow"><span>${i("offer")}</span></p>
            <h2 class="ti-title">${i("valuation")}</h2>
            <div class="ti-result-value">${s.finalPrice.toFixed(2)} ${i("currency")}</div>
            ${s.variantPrice!==void 0&&s.partsTotal!==void 0?p`<p class="ti-result-breakdown">
                    ${i("device-value")}: ${s.variantPrice.toFixed(2)} ${i("currency")} + ${i("parts-adjustment")}: ${s.partsTotal.toFixed(2)} ${i("currency")}
                </p>`:null}

            <div class="ti-actions">
                <button class="ti-btn is-secondary" @click=${t}>${i("decline")}</button>
                <button class="ti-btn is-primary" @click=${e}>${i("accept-offer")}</button>
            </div>
        </div>
    `}function rt(r,e){return p`
        <div class="ti-result">
            <p class="ti-eyebrow"><span>${e("result")}</span></p>
            <h2 class="ti-title">${e("not-eligible")}</h2>
            <p class="ti-subtitle">${e("not-eligible-subtitle")}</p>
            <div class="ti-actions">
                <span></span>
                <button class="ti-btn is-primary" @click=${()=>r({type:"RESTART"})}>${e("start-over")}</button>
            </div>
        </div>
    `}const Z={en:{"trade-in":"Trade-in valuation","select-device":"Select your device","choose-device":"Choose your model and storage to get started.",model:"Model","choose-model":"Choose a model",variant:"Storage / Variant","choose-variant":"Choose a variant",continue:"Continue",back:"Back",next:"Next",step:"Step",of:"of","almost-done":"Almost done",details:"Your details","contact-subtitle":"We'll use these to send your trade-in quote.","full-name":"Full name",email:"Email","phone-number":"Phone number",calculating:"Calculating...","get-quote":"Get my quote",offer:"Your offer",valuation:"Trade-in valuation",decline:"Decline","accept-offer":"Accept offer",result:"Result","not-eligible":"We're unable to provide a quote","not-eligible-subtitle":"Based on your answers, this device isn't eligible for trade-in right now.","start-over":"Start over","repair-details":"3rd-party repair details","repaired-parts":"Repaired parts","repair-subtitle":"You told us this device was repaired locally / by a 3rd party. Tell us which parts were replaced.","no-parts":"No parts found for this device.","replaced-part":"Replaced by a local / 3rd-party shop","device-value":"Device value","parts-adjustment":"Parts adjustment",currency:"EGP","loading-questionnaire":"Loading questionnaire...","generic-error":"Something went wrong.","error-missing-client":"This calculator is not configured correctly. The embed client ID is missing.","error-invalid-client":"This embed client ID is invalid or expired. Please ask the website owner to generate a new key from the vendor dashboard.","error-suspended-client":"This trade-in calculator is temporarily unavailable because the vendor account is suspended.","error-origin":"This calculator is not authorized to run on this website. Please contact the website owner.","error-rate-limit":"Too many requests were sent. Please wait a moment and try again.",questions:{power:{title:"Power & Functionality",question:"Is your device functional and able to turn on?",options:{yes:"Yes, it works perfectly",no:"No, it won't turn on"}},repair_history:{title:"Repair History",question:"Has your device been repaired before?",options:{never:"Never repaired",authorized:"Authorised Service",local:"Local / 3rd Party"}},battery_health:{title:"Battery Health",question:"What is the maximum capacity of your battery?",options:{"90+":"90% or higher","85-89":"85% - 89%","80-84":"80% - 84%",below80:"Below 80% / Service"}},screen_body:{title:"Screen & Body",question:"Is the screen and back glass completely intact with NO cracks?",options:{yes:"Yes, perfect condition",no:"No, there are cracks"},checkboxes:{"Front Screen":"Front Screen","Back Glass":"Back Glass"}},cosmetics:{title:"Cosmetics",question:"Are there any scratches on the body or screen?",options:{none:"No scratches (Mint)",light:"Light scratches",visible:"Visible / Deep"}},hardware:{title:"Hardware Check",question:"Do all buttons, speakers, and ports work properly?",options:{yes:"Yes, all working",no:"No, some issues"},checkboxes:{"Charging Port":"Charging Port","Volume Buttons":"Volume Buttons","Power Button":"Power Button",Earpiece:"Earpiece",Loudspeaker:"Loudspeaker","Vibration Motor":"Vibration Motor"}},cameras:{title:"Cameras",question:"Test your cameras. Do they work without spots or shaking?",options:{yes:"Yes, crystal clear",no:"No, I see issues"},checkboxes:{"Front Camera":"Front Camera","Back Camera":"Back Camera"}},connectivity:{title:"Connectivity",question:"Do Wi-Fi, Bluetooth, GPS, and NFC work?",options:{yes:"Yes, all connect",no:"No, connectivity issues"},checkboxes:{Network:"Network",WiFi:"Wi-Fi",Bluetooth:"Bluetooth",NFC:"NFC",GPS:"GPS"}},biometrics:{title:"Biometrics",question:"Does Face ID or Fingerprint sensor work properly?",options:{yes:"Yes, works fine",no:"No, not working"}}}},ar:{"trade-in":"تقييم الجهاز","select-device":"اختر جهازك","choose-device":"اختر موديل الجهاز والسعة للبدء.",model:"الموديل","choose-model":"اختر الموديل",variant:"السعة / الإصدار","choose-variant":"اختر الإصدار",continue:"متابعة",back:"رجوع",next:"التالي",step:"الخطوة",of:"من","almost-done":"أوشكت على الانتهاء",details:"بياناتك","contact-subtitle":"سنستخدم هذه البيانات لإرسال عرض الاستبدال الخاص بك.","full-name":"الاسم الكامل",email:"البريد الإلكتروني","phone-number":"رقم الهاتف",calculating:"جاري الحساب...","get-quote":"احصل على عرض السعر",offer:"عرضك",valuation:"تقييم الجهاز",decline:"رفض","accept-offer":"قبول العرض",result:"النتيجة","not-eligible":"لا يمكننا تقديم عرض سعر","not-eligible-subtitle":"بناءً على إجاباتك، هذا الجهاز غير مؤهل للاستبدال حالياً.","start-over":"البدء من جديد","repair-details":"تفاصيل الإصلاح الخارجي","repaired-parts":"الأجزاء التي تم إصلاحها","repair-subtitle":"ذكرت أن الجهاز تم إصلاحه محلياً أو لدى طرف ثالث. حدد الأجزاء التي تم استبدالها.","no-parts":"لا توجد أجزاء لهذا الجهاز.","replaced-part":"تم استبداله لدى محل إصلاح محلي أو طرف ثالث","device-value":"قيمة الجهاز","parts-adjustment":"تعديل الأجزاء",currency:"جنيه","loading-questionnaire":"جاري تحميل الأسئلة...","generic-error":"حدث خطأ ما.","error-missing-client":"لم يتم إعداد حاسبة الاستبدال بشكل صحيح. مفتاح العميل غير موجود.","error-invalid-client":"مفتاح العميل غير صحيح أو منتهي الصلاحية. يرجى مطالبة مسؤول الموقع بإنشاء مفتاح جديد من لوحة تحكم البائع.","error-suspended-client":"حاسبة الاستبدال غير متاحة مؤقتاً لأن حساب البائع موقوف.","error-origin":"هذه الحاسبة غير مصرح لها بالعمل على هذا الموقع. يرجى التواصل مع مسؤول الموقع.","error-rate-limit":"تم إرسال طلبات كثيرة. يرجى الانتظار قليلاً ثم المحاولة مرة أخرى.",questions:{power:{title:"الطاقة والوظائف",question:"هل الجهاز يعمل ويمكن تشغيله؟",options:{yes:"نعم، يعمل بشكل مثالي",no:"لا، لا يعمل"}},repair_history:{title:"سجل الإصلاح",question:"هل تم إصلاح الجهاز من قبل؟",options:{never:"لم يتم إصلاحه",authorized:"مركز خدمة معتمد",local:"إصلاح محلي / طرف ثالث"}},battery_health:{title:"صحة البطارية",question:"ما السعة القصوى لبطارية الجهاز؟",options:{"90+":"90% أو أكثر","85-89":"85% - 89%","80-84":"80% - 84%",below80:"أقل من 80% / تحتاج إلى خدمة"}},screen_body:{title:"الشاشة والهيكل",question:"هل الشاشة والزجاج الخلفي سليمان تماماً وبدون كسور؟",options:{yes:"نعم، بحالة ممتازة",no:"لا، توجد كسور"},checkboxes:{"Front Screen":"الشاشة الأمامية","Back Glass":"الزجاج الخلفي"}},cosmetics:{title:"المظهر الخارجي",question:"هل توجد خدوش على الهيكل أو الشاشة؟",options:{none:"لا توجد خدوش (كالجديد)",light:"خدوش خفيفة",visible:"خدوش واضحة / عميقة"}},hardware:{title:"فحص الأجهزة",question:"هل تعمل الأزرار والسماعات والمنافذ بشكل صحيح؟",options:{yes:"نعم، كلها تعمل",no:"لا، توجد بعض المشاكل"},checkboxes:{"Charging Port":"منفذ الشحن","Volume Buttons":"أزرار الصوت","Power Button":"زر التشغيل",Earpiece:"سماعة المكالمات",Loudspeaker:"السماعة الخارجية","Vibration Motor":"محرك الاهتزاز"}},cameras:{title:"الكاميرات",question:"اختبر الكاميرات. هل تعمل بدون بقع أو اهتزاز؟",options:{yes:"نعم، الصورة واضحة",no:"لا، توجد مشاكل"},checkboxes:{"Front Camera":"الكاميرا الأمامية","Back Camera":"الكاميرا الخلفية"}},connectivity:{title:"الاتصال",question:"هل تعمل شبكة الهاتف والواي فاي والبلوتوث وGPS وNFC؟",options:{yes:"نعم، كلها تعمل",no:"لا، توجد مشاكل في الاتصال"},checkboxes:{Network:"الشبكة",WiFi:"الواي فاي",Bluetooth:"البلوتوث",NFC:"NFC",GPS:"GPS"}},biometrics:{title:"المقاييس الحيوية",question:"هل تعمل بصمة الوجه أو بصمة الإصبع بشكل صحيح؟",options:{yes:"نعم، تعمل جيداً",no:"لا، لا تعمل"}}}}};function B(r){return String(r||"en").toLowerCase().startsWith("ar")?"ar":"en"}function ve(r){const e=Z[B(r)];return t=>e[t]??Z.en[t]??t}function st(r,e){const t=Z[B(e)].questions[r.key];return t?{...r,title:t.title,question:t.question,options:r.options.map(i=>{var s,n;return{...i,label:((s=t.options)==null?void 0:s[i.value])??i.label,conditional_checkboxes:(n=i.conditional_checkboxes)==null?void 0:n.map(o=>{var a;return{...o,label:((a=t.checkboxes)==null?void 0:a[o.value])??o.label}})}})}:r}function nt(r){return B(r)==="ar"}class z extends U{constructor(){super();F(this,"dispatch",t=>{this._state=je(this._state,t),this.requestUpdate(),t.type==="SELECT_PRODUCT"&&this._loadVariants(t.productId),t.type==="GO_TO_QUESTIONNAIRE"&&this._state.selectedProductId&&this._loadParts(this._state.selectedProductId)});this.apiBase="",this.clientId="",this.primaryColor="",this.language="en",this.lang="",this._state=ye()}connectedCallback(){super.connectedCallback(),this._ensureFontLoaded(),this._api=new Fe(this.apiBase,this.clientId),this._loadInitialData()}_ensureFontLoaded(){const t="trade-in-widget-ibm-plex-sans-arabic";if(document.getElementById(t))return;const i=document.createElement("link");i.rel="preconnect",i.href="https://fonts.googleapis.com",document.head.appendChild(i);const s=document.createElement("link");s.id=t,s.rel="stylesheet",s.href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap",document.head.appendChild(s)}attributeChangedCallback(t,i,s){super.attributeChangedCallback(t,i,s),t==="primary-color"&&s&&this.style.setProperty("--ti-primary",s),(t==="language"||t==="lang")&&i!==s&&this.requestUpdate()}async _loadInitialData(){this.dispatch({type:"LOADING",value:!0});try{const[t,i]=await Promise.all([this._api.getProducts(),this._api.getQuestionnaire()]);this.dispatch({type:"PRODUCTS_LOADED",products:t}),this.dispatch({type:"QUESTIONNAIRE_LOADED",questionnaire:i})}catch(t){this.dispatch({type:"ERROR",message:this._friendlyError(t)})}finally{this.dispatch({type:"LOADING",value:!1})}}async _loadVariants(t){this.dispatch({type:"LOADING",value:!0});try{const i=await this._api.getVariants(t);this.dispatch({type:"VARIANTS_LOADED",variants:i})}catch(i){this.dispatch({type:"ERROR",message:this._friendlyError(i)})}finally{this.dispatch({type:"LOADING",value:!1})}}async _loadParts(t){this.dispatch({type:"LOADING",value:!0});try{const i=await this._api.getParts(t);this.dispatch({type:"PARTS_LOADED",parts:i})}catch(i){this.dispatch({type:"ERROR",message:this._friendlyError(i)})}finally{this.dispatch({type:"LOADING",value:!1})}}async _submitCalculation(){this.dispatch({type:"LOADING",value:!0});try{const t=$e(this._state),i=await this._api.calculate(t);this.dispatch({type:"RESULT_LOADED",result:i})}catch(t){this.dispatch({type:"ERROR",message:this._friendlyError(t)})}finally{this.dispatch({type:"LOADING",value:!1})}}_friendlyError(t){const i=B(this.getAttribute("language")||this.getAttribute("lang")||"en"),s=ve(i),n={missing_client_id:"error-missing-client",invalid_client_id:"error-invalid-client",suspended_vendor:"error-suspended-client",origin_not_allowed:"error-origin"};return(t==null?void 0:t.status)===429?s("error-rate-limit"):s(n[t==null?void 0:t.code]??"generic-error")}_emit(t,i){this.dispatchEvent(new CustomEvent(t,{detail:i,bubbles:!0,composed:!0}));const s=t==="trade-in:accepted"?"onaccepted":t==="trade-in:rejected"?"onrejected":null;s&&this.dispatchEvent(new CustomEvent(s,{detail:i,bubbles:!0,composed:!0}))}render(){const t=this._state,i=B(this.getAttribute("language")||this.getAttribute("lang")||"en"),s=ve(i),n=a=>st(a,i);let o;switch(t.step){case f.DEVICE_SELECT:o=Ye(t,this.dispatch,s);break;case f.QUESTIONNAIRE:o=Ke(t,this.dispatch,s,n);break;case f.PARTS_SELECT:o=Xe(t,this.dispatch,s);break;case f.CONTACT:o=tt(t,this.dispatch,()=>this._submitCalculation(),s);break;case f.RESULT:o=it(t,()=>this._emit("trade-in:accepted",{result:t.result,payload:$e(t)}),()=>this._emit("trade-in:rejected",{result:t.result}),s);break;case f.NOT_ELIGIBLE:o=rt(this.dispatch,s);break;default:o=p`<p>${s("generic-error")}</p>`}return p`<div class="ti-card" dir=${nt(i)?"rtl":"ltr"}>${o}</div>`}}return F(z,"styles",ze),F(z,"properties",{apiBase:{attribute:"api-base",type:String},clientId:{attribute:"client-id",type:String},primaryColor:{attribute:"primary-color",type:String},language:{attribute:"language",type:String},lang:{attribute:"lang",type:String}}),customElements.define("trade-in-calculator",z),$.TradeInCalculator=z,Object.defineProperty($,Symbol.toStringTag,{value:"Module"}),$}({});
