(function(){const t=document.createElement("link").relList;if(t&&t.supports&&t.supports("modulepreload"))return;for(const o of document.querySelectorAll('link[rel="modulepreload"]'))r(o);new MutationObserver(o=>{for(const s of o)if(s.type==="childList")for(const i of s.addedNodes)i.tagName==="LINK"&&i.rel==="modulepreload"&&r(i)}).observe(document,{childList:!0,subtree:!0});function n(o){const s={};return o.integrity&&(s.integrity=o.integrity),o.referrerPolicy&&(s.referrerPolicy=o.referrerPolicy),o.crossOrigin==="use-credentials"?s.credentials="include":o.crossOrigin==="anonymous"?s.credentials="omit":s.credentials="same-origin",s}function r(o){if(o.ep)return;o.ep=!0;const s=n(o);fetch(o.href,s)}})();/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and 
 * limitations under the License.
 *//**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Vt=function(e){const t=[];let n=0;for(let r=0;r<e.length;r++){let o=e.charCodeAt(r);o<128?t[n++]=o:o<2048?(t[n++]=o>>6|192,t[n++]=o&63|128):(o&64512)===55296&&r+1<e.length&&(e.charCodeAt(r+1)&64512)===56320?(o=65536+((o&1023)<<10)+(e.charCodeAt(++r)&1023),t[n++]=o>>18|240,t[n++]=o>>12&63|128,t[n++]=o>>6&63|128,t[n++]=o&63|128):(t[n++]=o>>12|224,t[n++]=o>>6&63|128,t[n++]=o&63|128)}return t},Gn=function(e){const t=[];let n=0,r=0;for(;n<e.length;){const o=e[n++];if(o<128)t[r++]=String.fromCharCode(o);else if(o>191&&o<224){const s=e[n++];t[r++]=String.fromCharCode((o&31)<<6|s&63)}else if(o>239&&o<365){const s=e[n++],i=e[n++],a=e[n++],l=((o&7)<<18|(s&63)<<12|(i&63)<<6|a&63)-65536;t[r++]=String.fromCharCode(55296+(l>>10)),t[r++]=String.fromCharCode(56320+(l&1023))}else{const s=e[n++],i=e[n++];t[r++]=String.fromCharCode((o&15)<<12|(s&63)<<6|i&63)}}return t.join("")},qt={byteToCharMap_:null,charToByteMap_:null,byteToCharMapWebSafe_:null,charToByteMapWebSafe_:null,ENCODED_VALS_BASE:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789",get ENCODED_VALS(){return this.ENCODED_VALS_BASE+"+/="},get ENCODED_VALS_WEBSAFE(){return this.ENCODED_VALS_BASE+"-_."},HAS_NATIVE_SUPPORT:typeof atob=="function",encodeByteArray(e,t){if(!Array.isArray(e))throw Error("encodeByteArray takes an array as a parameter");this.init_();const n=t?this.byteToCharMapWebSafe_:this.byteToCharMap_,r=[];for(let o=0;o<e.length;o+=3){const s=e[o],i=o+1<e.length,a=i?e[o+1]:0,l=o+2<e.length,c=l?e[o+2]:0,d=s>>2,h=(s&3)<<4|a>>4;let y=(a&15)<<2|c>>6,g=c&63;l||(g=64,i||(y=64)),r.push(n[d],n[h],n[y],n[g])}return r.join("")},encodeString(e,t){return this.HAS_NATIVE_SUPPORT&&!t?btoa(e):this.encodeByteArray(Vt(e),t)},decodeString(e,t){return this.HAS_NATIVE_SUPPORT&&!t?atob(e):Gn(this.decodeStringToByteArray(e,t))},decodeStringToByteArray(e,t){this.init_();const n=t?this.charToByteMapWebSafe_:this.charToByteMap_,r=[];for(let o=0;o<e.length;){const s=n[e.charAt(o++)],a=o<e.length?n[e.charAt(o)]:0;++o;const c=o<e.length?n[e.charAt(o)]:64;++o;const h=o<e.length?n[e.charAt(o)]:64;if(++o,s==null||a==null||c==null||h==null)throw new Yn;const y=s<<2|a>>4;if(r.push(y),c!==64){const g=a<<4&240|c>>2;if(r.push(g),h!==64){const f=c<<6&192|h;r.push(f)}}}return r},init_(){if(!this.byteToCharMap_){this.byteToCharMap_={},this.charToByteMap_={},this.byteToCharMapWebSafe_={},this.charToByteMapWebSafe_={};for(let e=0;e<this.ENCODED_VALS.length;e++)this.byteToCharMap_[e]=this.ENCODED_VALS.charAt(e),this.charToByteMap_[this.byteToCharMap_[e]]=e,this.byteToCharMapWebSafe_[e]=this.ENCODED_VALS_WEBSAFE.charAt(e),this.charToByteMapWebSafe_[this.byteToCharMapWebSafe_[e]]=e,e>=this.ENCODED_VALS_BASE.length&&(this.charToByteMap_[this.ENCODED_VALS_WEBSAFE.charAt(e)]=e,this.charToByteMapWebSafe_[this.ENCODED_VALS.charAt(e)]=e)}}};class Yn extends Error{constructor(){super(...arguments),this.name="DecodeBase64StringError"}}const Xn=function(e){const t=Vt(e);return qt.encodeByteArray(t,!0)},Wt=function(e){return Xn(e).replace(/\./g,"")},Qn=function(e){try{return qt.decodeString(e,!0)}catch(t){console.error("base64Decode failed: ",t)}return null};/**
 * @license
 * Copyright 2022 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Zn(){if(typeof self<"u")return self;if(typeof window<"u")return window;if(typeof global<"u")return global;throw new Error("Unable to locate global object.")}/**
 * @license
 * Copyright 2022 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const er=()=>Zn().__FIREBASE_DEFAULTS__,tr=()=>{if(typeof process>"u"||typeof process.env>"u")return;const e={}.__FIREBASE_DEFAULTS__;if(e)return JSON.parse(e)},nr=()=>{if(typeof document>"u")return;let e;try{e=document.cookie.match(/__FIREBASE_DEFAULTS__=([^;]+)/)}catch{return}const t=e&&Qn(e[1]);return t&&JSON.parse(t)},rr=()=>{try{return er()||tr()||nr()}catch(e){console.info(`Unable to get __FIREBASE_DEFAULTS__ due to: ${e}`);return}},or=()=>{var e;return(e=rr())===null||e===void 0?void 0:e.config};/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class sr{constructor(){this.reject=()=>{},this.resolve=()=>{},this.promise=new Promise((t,n)=>{this.resolve=t,this.reject=n})}wrapCallback(t){return(n,r)=>{n?this.reject(n):this.resolve(r),typeof t=="function"&&(this.promise.catch(()=>{}),t.length===1?t(n):t(n,r))}}}function zt(){try{return typeof indexedDB=="object"}catch{return!1}}function Kt(){return new Promise((e,t)=>{try{let n=!0;const r="validate-browser-context-for-indexeddb-analytics-module",o=self.indexedDB.open(r);o.onsuccess=()=>{o.result.close(),n||self.indexedDB.deleteDatabase(r),e(!0)},o.onupgradeneeded=()=>{n=!1},o.onerror=()=>{var s;t(((s=o.error)===null||s===void 0?void 0:s.message)||"")}}catch(n){t(n)}})}function ir(){return!(typeof navigator>"u"||!navigator.cookieEnabled)}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const ar="FirebaseError";class q extends Error{constructor(t,n,r){super(n),this.code=t,this.customData=r,this.name=ar,Object.setPrototypeOf(this,q.prototype),Error.captureStackTrace&&Error.captureStackTrace(this,ae.prototype.create)}}class ae{constructor(t,n,r){this.service=t,this.serviceName=n,this.errors=r}create(t,...n){const r=n[0]||{},o=`${this.service}/${t}`,s=this.errors[t],i=s?cr(s,r):"Error",a=`${this.serviceName}: ${i} (${o}).`;return new q(o,a,r)}}function cr(e,t){return e.replace(ur,(n,r)=>{const o=t[r];return o!=null?String(o):`<${r}?>`})}const ur=/\{\$([^}]+)}/g;function Pe(e,t){if(e===t)return!0;const n=Object.keys(e),r=Object.keys(t);for(const o of n){if(!r.includes(o))return!1;const s=e[o],i=t[o];if(ft(s)&&ft(i)){if(!Pe(s,i))return!1}else if(s!==i)return!1}for(const o of r)if(!n.includes(o))return!1;return!0}function ft(e){return e!==null&&typeof e=="object"}/**
 * @license
 * Copyright 2021 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Ke(e){return e&&e._delegate?e._delegate:e}class B{constructor(t,n,r){this.name=t,this.instanceFactory=n,this.type=r,this.multipleInstances=!1,this.serviceProps={},this.instantiationMode="LAZY",this.onInstanceCreated=null}setInstantiationMode(t){return this.instantiationMode=t,this}setMultipleInstances(t){return this.multipleInstances=t,this}setServiceProps(t){return this.serviceProps=t,this}setInstanceCreatedCallback(t){return this.onInstanceCreated=t,this}}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const P="[DEFAULT]";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class lr{constructor(t,n){this.name=t,this.container=n,this.component=null,this.instances=new Map,this.instancesDeferred=new Map,this.instancesOptions=new Map,this.onInitCallbacks=new Map}get(t){const n=this.normalizeInstanceIdentifier(t);if(!this.instancesDeferred.has(n)){const r=new sr;if(this.instancesDeferred.set(n,r),this.isInitialized(n)||this.shouldAutoInitialize())try{const o=this.getOrInitializeService({instanceIdentifier:n});o&&r.resolve(o)}catch{}}return this.instancesDeferred.get(n).promise}getImmediate(t){var n;const r=this.normalizeInstanceIdentifier(t==null?void 0:t.identifier),o=(n=t==null?void 0:t.optional)!==null&&n!==void 0?n:!1;if(this.isInitialized(r)||this.shouldAutoInitialize())try{return this.getOrInitializeService({instanceIdentifier:r})}catch(s){if(o)return null;throw s}else{if(o)return null;throw Error(`Service ${this.name} is not available`)}}getComponent(){return this.component}setComponent(t){if(t.name!==this.name)throw Error(`Mismatching Component ${t.name} for Provider ${this.name}.`);if(this.component)throw Error(`Component for ${this.name} has already been provided`);if(this.component=t,!!this.shouldAutoInitialize()){if(fr(t))try{this.getOrInitializeService({instanceIdentifier:P})}catch{}for(const[n,r]of this.instancesDeferred.entries()){const o=this.normalizeInstanceIdentifier(n);try{const s=this.getOrInitializeService({instanceIdentifier:o});r.resolve(s)}catch{}}}}clearInstance(t=P){this.instancesDeferred.delete(t),this.instancesOptions.delete(t),this.instances.delete(t)}async delete(){const t=Array.from(this.instances.values());await Promise.all([...t.filter(n=>"INTERNAL"in n).map(n=>n.INTERNAL.delete()),...t.filter(n=>"_delete"in n).map(n=>n._delete())])}isComponentSet(){return this.component!=null}isInitialized(t=P){return this.instances.has(t)}getOptions(t=P){return this.instancesOptions.get(t)||{}}initialize(t={}){const{options:n={}}=t,r=this.normalizeInstanceIdentifier(t.instanceIdentifier);if(this.isInitialized(r))throw Error(`${this.name}(${r}) has already been initialized`);if(!this.isComponentSet())throw Error(`Component ${this.name} has not been registered yet`);const o=this.getOrInitializeService({instanceIdentifier:r,options:n});for(const[s,i]of this.instancesDeferred.entries()){const a=this.normalizeInstanceIdentifier(s);r===a&&i.resolve(o)}return o}onInit(t,n){var r;const o=this.normalizeInstanceIdentifier(n),s=(r=this.onInitCallbacks.get(o))!==null&&r!==void 0?r:new Set;s.add(t),this.onInitCallbacks.set(o,s);const i=this.instances.get(o);return i&&t(i,o),()=>{s.delete(t)}}invokeOnInitCallbacks(t,n){const r=this.onInitCallbacks.get(n);if(r)for(const o of r)try{o(t,n)}catch{}}getOrInitializeService({instanceIdentifier:t,options:n={}}){let r=this.instances.get(t);if(!r&&this.component&&(r=this.component.instanceFactory(this.container,{instanceIdentifier:dr(t),options:n}),this.instances.set(t,r),this.instancesOptions.set(t,n),this.invokeOnInitCallbacks(r,t),this.component.onInstanceCreated))try{this.component.onInstanceCreated(this.container,t,r)}catch{}return r||null}normalizeInstanceIdentifier(t=P){return this.component?this.component.multipleInstances?t:P:t}shouldAutoInitialize(){return!!this.component&&this.component.instantiationMode!=="EXPLICIT"}}function dr(e){return e===P?void 0:e}function fr(e){return e.instantiationMode==="EAGER"}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class hr{constructor(t){this.name=t,this.providers=new Map}addComponent(t){const n=this.getProvider(t.name);if(n.isComponentSet())throw new Error(`Component ${t.name} has already been registered with ${this.name}`);n.setComponent(t)}addOrOverwriteComponent(t){this.getProvider(t.name).isComponentSet()&&this.providers.delete(t.name),this.addComponent(t)}getProvider(t){if(this.providers.has(t))return this.providers.get(t);const n=new lr(t,this);return this.providers.set(t,n),n}getProviders(){return Array.from(this.providers.values())}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */var b;(function(e){e[e.DEBUG=0]="DEBUG",e[e.VERBOSE=1]="VERBOSE",e[e.INFO=2]="INFO",e[e.WARN=3]="WARN",e[e.ERROR=4]="ERROR",e[e.SILENT=5]="SILENT"})(b||(b={}));const pr={debug:b.DEBUG,verbose:b.VERBOSE,info:b.INFO,warn:b.WARN,error:b.ERROR,silent:b.SILENT},mr=b.INFO,gr={[b.DEBUG]:"log",[b.VERBOSE]:"log",[b.INFO]:"info",[b.WARN]:"warn",[b.ERROR]:"error"},br=(e,t,...n)=>{if(t<e.logLevel)return;const r=new Date().toISOString(),o=gr[t];if(o)console[o](`[${r}]  ${e.name}:`,...n);else throw new Error(`Attempted to log a message with an invalid logType (value: ${t})`)};class yr{constructor(t){this.name=t,this._logLevel=mr,this._logHandler=br,this._userLogHandler=null}get logLevel(){return this._logLevel}set logLevel(t){if(!(t in b))throw new TypeError(`Invalid value "${t}" assigned to \`logLevel\``);this._logLevel=t}setLogLevel(t){this._logLevel=typeof t=="string"?pr[t]:t}get logHandler(){return this._logHandler}set logHandler(t){if(typeof t!="function")throw new TypeError("Value assigned to `logHandler` must be a function");this._logHandler=t}get userLogHandler(){return this._userLogHandler}set userLogHandler(t){this._userLogHandler=t}debug(...t){this._userLogHandler&&this._userLogHandler(this,b.DEBUG,...t),this._logHandler(this,b.DEBUG,...t)}log(...t){this._userLogHandler&&this._userLogHandler(this,b.VERBOSE,...t),this._logHandler(this,b.VERBOSE,...t)}info(...t){this._userLogHandler&&this._userLogHandler(this,b.INFO,...t),this._logHandler(this,b.INFO,...t)}warn(...t){this._userLogHandler&&this._userLogHandler(this,b.WARN,...t),this._logHandler(this,b.WARN,...t)}error(...t){this._userLogHandler&&this._userLogHandler(this,b.ERROR,...t),this._logHandler(this,b.ERROR,...t)}}const wr=(e,t)=>t.some(n=>e instanceof n);let ht,pt;function Er(){return ht||(ht=[IDBDatabase,IDBObjectStore,IDBIndex,IDBCursor,IDBTransaction])}function Sr(){return pt||(pt=[IDBCursor.prototype.advance,IDBCursor.prototype.continue,IDBCursor.prototype.continuePrimaryKey])}const Jt=new WeakMap,Me=new WeakMap,Gt=new WeakMap,ge=new WeakMap,Je=new WeakMap;function Ar(e){const t=new Promise((n,r)=>{const o=()=>{e.removeEventListener("success",s),e.removeEventListener("error",i)},s=()=>{n(R(e.result)),o()},i=()=>{r(e.error),o()};e.addEventListener("success",s),e.addEventListener("error",i)});return t.then(n=>{n instanceof IDBCursor&&Jt.set(n,e)}).catch(()=>{}),Je.set(t,e),t}function Ir(e){if(Me.has(e))return;const t=new Promise((n,r)=>{const o=()=>{e.removeEventListener("complete",s),e.removeEventListener("error",i),e.removeEventListener("abort",i)},s=()=>{n(),o()},i=()=>{r(e.error||new DOMException("AbortError","AbortError")),o()};e.addEventListener("complete",s),e.addEventListener("error",i),e.addEventListener("abort",i)});Me.set(e,t)}let xe={get(e,t,n){if(e instanceof IDBTransaction){if(t==="done")return Me.get(e);if(t==="objectStoreNames")return e.objectStoreNames||Gt.get(e);if(t==="store")return n.objectStoreNames[1]?void 0:n.objectStore(n.objectStoreNames[0])}return R(e[t])},set(e,t,n){return e[t]=n,!0},has(e,t){return e instanceof IDBTransaction&&(t==="done"||t==="store")?!0:t in e}};function Tr(e){xe=e(xe)}function _r(e){return e===IDBDatabase.prototype.transaction&&!("objectStoreNames"in IDBTransaction.prototype)?function(t,...n){const r=e.call(be(this),t,...n);return Gt.set(r,t.sort?t.sort():[t]),R(r)}:Sr().includes(e)?function(...t){return e.apply(be(this),t),R(Jt.get(this))}:function(...t){return R(e.apply(be(this),t))}}function Or(e){return typeof e=="function"?_r(e):(e instanceof IDBTransaction&&Ir(e),wr(e,Er())?new Proxy(e,xe):e)}function R(e){if(e instanceof IDBRequest)return Ar(e);if(ge.has(e))return ge.get(e);const t=Or(e);return t!==e&&(ge.set(e,t),Je.set(t,e)),t}const be=e=>Je.get(e);function Dr(e,t,{blocked:n,upgrade:r,blocking:o,terminated:s}={}){const i=indexedDB.open(e,t),a=R(i);return r&&i.addEventListener("upgradeneeded",l=>{r(R(i.result),l.oldVersion,l.newVersion,R(i.transaction),l)}),n&&i.addEventListener("blocked",l=>n(l.oldVersion,l.newVersion,l)),a.then(l=>{s&&l.addEventListener("close",()=>s()),o&&l.addEventListener("versionchange",c=>o(c.oldVersion,c.newVersion,c))}).catch(()=>{}),a}const Cr=["get","getKey","getAll","getAllKeys","count"],vr=["put","add","delete","clear"],ye=new Map;function mt(e,t){if(!(e instanceof IDBDatabase&&!(t in e)&&typeof t=="string"))return;if(ye.get(t))return ye.get(t);const n=t.replace(/FromIndex$/,""),r=t!==n,o=vr.includes(n);if(!(n in(r?IDBIndex:IDBObjectStore).prototype)||!(o||Cr.includes(n)))return;const s=async function(i,...a){const l=this.transaction(i,o?"readwrite":"readonly");let c=l.store;return r&&(c=c.index(a.shift())),(await Promise.all([c[n](...a),o&&l.done]))[0]};return ye.set(t,s),s}Tr(e=>({...e,get:(t,n,r)=>mt(t,n)||e.get(t,n,r),has:(t,n)=>!!mt(t,n)||e.has(t,n)}));/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Rr{constructor(t){this.container=t}getPlatformInfoString(){return this.container.getProviders().map(n=>{if(Nr(n)){const r=n.getImmediate();return`${r.library}/${r.version}`}else return null}).filter(n=>n).join(" ")}}function Nr(e){const t=e.getComponent();return(t==null?void 0:t.type)==="VERSION"}const Le="@firebase/app",gt="0.9.9";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const x=new yr("@firebase/app"),kr="@firebase/app-compat",Br="@firebase/analytics-compat",Pr="@firebase/analytics",Mr="@firebase/app-check-compat",xr="@firebase/app-check",Lr="@firebase/auth",Fr="@firebase/auth-compat",$r="@firebase/database",jr="@firebase/database-compat",Hr="@firebase/functions",Ur="@firebase/functions-compat",Vr="@firebase/installations",qr="@firebase/installations-compat",Wr="@firebase/messaging",zr="@firebase/messaging-compat",Kr="@firebase/performance",Jr="@firebase/performance-compat",Gr="@firebase/remote-config",Yr="@firebase/remote-config-compat",Xr="@firebase/storage",Qr="@firebase/storage-compat",Zr="@firebase/firestore",eo="@firebase/firestore-compat",to="firebase";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Fe="[DEFAULT]",no={[Le]:"fire-core",[kr]:"fire-core-compat",[Pr]:"fire-analytics",[Br]:"fire-analytics-compat",[xr]:"fire-app-check",[Mr]:"fire-app-check-compat",[Lr]:"fire-auth",[Fr]:"fire-auth-compat",[$r]:"fire-rtdb",[jr]:"fire-rtdb-compat",[Hr]:"fire-fn",[Ur]:"fire-fn-compat",[Vr]:"fire-iid",[qr]:"fire-iid-compat",[Wr]:"fire-fcm",[zr]:"fire-fcm-compat",[Kr]:"fire-perf",[Jr]:"fire-perf-compat",[Gr]:"fire-rc",[Yr]:"fire-rc-compat",[Xr]:"fire-gcs",[Qr]:"fire-gcs-compat",[Zr]:"fire-fst",[eo]:"fire-fst-compat","fire-js":"fire-js",[to]:"fire-js-all"};/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const re=new Map,$e=new Map;function ro(e,t){try{e.container.addComponent(t)}catch(n){x.debug(`Component ${t.name} failed to register with FirebaseApp ${e.name}`,n)}}function L(e){const t=e.name;if($e.has(t))return x.debug(`There were multiple attempts to register component ${t}.`),!1;$e.set(t,e);for(const n of re.values())ro(n,e);return!0}function Ge(e,t){const n=e.container.getProvider("heartbeat").getImmediate({optional:!0});return n&&n.triggerHeartbeat(),e.container.getProvider(t)}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const oo={["no-app"]:"No Firebase App '{$appName}' has been created - call Firebase App.initializeApp()",["bad-app-name"]:"Illegal App name: '{$appName}",["duplicate-app"]:"Firebase App named '{$appName}' already exists with different options or config",["app-deleted"]:"Firebase App named '{$appName}' already deleted",["no-options"]:"Need to provide options, when not being deployed to hosting via source.",["invalid-app-argument"]:"firebase.{$appName}() takes either no argument or a Firebase App instance.",["invalid-log-argument"]:"First argument to `onLog` must be null or a function.",["idb-open"]:"Error thrown when opening IndexedDB. Original error: {$originalErrorMessage}.",["idb-get"]:"Error thrown when reading from IndexedDB. Original error: {$originalErrorMessage}.",["idb-set"]:"Error thrown when writing to IndexedDB. Original error: {$originalErrorMessage}.",["idb-delete"]:"Error thrown when deleting from IndexedDB. Original error: {$originalErrorMessage}."},N=new ae("app","Firebase",oo);/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class so{constructor(t,n,r){this._isDeleted=!1,this._options=Object.assign({},t),this._config=Object.assign({},n),this._name=n.name,this._automaticDataCollectionEnabled=n.automaticDataCollectionEnabled,this._container=r,this.container.addComponent(new B("app",()=>this,"PUBLIC"))}get automaticDataCollectionEnabled(){return this.checkDestroyed(),this._automaticDataCollectionEnabled}set automaticDataCollectionEnabled(t){this.checkDestroyed(),this._automaticDataCollectionEnabled=t}get name(){return this.checkDestroyed(),this._name}get options(){return this.checkDestroyed(),this._options}get config(){return this.checkDestroyed(),this._config}get container(){return this._container}get isDeleted(){return this._isDeleted}set isDeleted(t){this._isDeleted=t}checkDestroyed(){if(this.isDeleted)throw N.create("app-deleted",{appName:this._name})}}function Yt(e,t={}){let n=e;typeof t!="object"&&(t={name:t});const r=Object.assign({name:Fe,automaticDataCollectionEnabled:!1},t),o=r.name;if(typeof o!="string"||!o)throw N.create("bad-app-name",{appName:String(o)});if(n||(n=or()),!n)throw N.create("no-options");const s=re.get(o);if(s){if(Pe(n,s.options)&&Pe(r,s.config))return s;throw N.create("duplicate-app",{appName:o})}const i=new hr(o);for(const l of $e.values())i.addComponent(l);const a=new so(n,r,i);return re.set(o,a),a}function io(e=Fe){const t=re.get(e);if(!t&&e===Fe)return Yt();if(!t)throw N.create("no-app",{appName:e});return t}function k(e,t,n){var r;let o=(r=no[e])!==null&&r!==void 0?r:e;n&&(o+=`-${n}`);const s=o.match(/\s|\//),i=t.match(/\s|\//);if(s||i){const a=[`Unable to register library "${o}" with version "${t}":`];s&&a.push(`library name "${o}" contains illegal characters (whitespace or "/")`),s&&i&&a.push("and"),i&&a.push(`version name "${t}" contains illegal characters (whitespace or "/")`),x.warn(a.join(" "));return}L(new B(`${o}-version`,()=>({library:o,version:t}),"VERSION"))}/**
 * @license
 * Copyright 2021 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const ao="firebase-heartbeat-database",co=1,K="firebase-heartbeat-store";let we=null;function Xt(){return we||(we=Dr(ao,co,{upgrade:(e,t)=>{switch(t){case 0:e.createObjectStore(K)}}}).catch(e=>{throw N.create("idb-open",{originalErrorMessage:e.message})})),we}async function uo(e){try{return(await Xt()).transaction(K).objectStore(K).get(Qt(e))}catch(t){if(t instanceof q)x.warn(t.message);else{const n=N.create("idb-get",{originalErrorMessage:t==null?void 0:t.message});x.warn(n.message)}}}async function bt(e,t){try{const r=(await Xt()).transaction(K,"readwrite");return await r.objectStore(K).put(t,Qt(e)),r.done}catch(n){if(n instanceof q)x.warn(n.message);else{const r=N.create("idb-set",{originalErrorMessage:n==null?void 0:n.message});x.warn(r.message)}}}function Qt(e){return`${e.name}!${e.options.appId}`}/**
 * @license
 * Copyright 2021 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const lo=1024,fo=30*24*60*60*1e3;class ho{constructor(t){this.container=t,this._heartbeatsCache=null;const n=this.container.getProvider("app").getImmediate();this._storage=new mo(n),this._heartbeatsCachePromise=this._storage.read().then(r=>(this._heartbeatsCache=r,r))}async triggerHeartbeat(){const n=this.container.getProvider("platform-logger").getImmediate().getPlatformInfoString(),r=yt();if(this._heartbeatsCache===null&&(this._heartbeatsCache=await this._heartbeatsCachePromise),!(this._heartbeatsCache.lastSentHeartbeatDate===r||this._heartbeatsCache.heartbeats.some(o=>o.date===r)))return this._heartbeatsCache.heartbeats.push({date:r,agent:n}),this._heartbeatsCache.heartbeats=this._heartbeatsCache.heartbeats.filter(o=>{const s=new Date(o.date).valueOf();return Date.now()-s<=fo}),this._storage.overwrite(this._heartbeatsCache)}async getHeartbeatsHeader(){if(this._heartbeatsCache===null&&await this._heartbeatsCachePromise,this._heartbeatsCache===null||this._heartbeatsCache.heartbeats.length===0)return"";const t=yt(),{heartbeatsToSend:n,unsentEntries:r}=po(this._heartbeatsCache.heartbeats),o=Wt(JSON.stringify({version:2,heartbeats:n}));return this._heartbeatsCache.lastSentHeartbeatDate=t,r.length>0?(this._heartbeatsCache.heartbeats=r,await this._storage.overwrite(this._heartbeatsCache)):(this._heartbeatsCache.heartbeats=[],this._storage.overwrite(this._heartbeatsCache)),o}}function yt(){return new Date().toISOString().substring(0,10)}function po(e,t=lo){const n=[];let r=e.slice();for(const o of e){const s=n.find(i=>i.agent===o.agent);if(s){if(s.dates.push(o.date),wt(n)>t){s.dates.pop();break}}else if(n.push({agent:o.agent,dates:[o.date]}),wt(n)>t){n.pop();break}r=r.slice(1)}return{heartbeatsToSend:n,unsentEntries:r}}class mo{constructor(t){this.app=t,this._canUseIndexedDBPromise=this.runIndexedDBEnvironmentCheck()}async runIndexedDBEnvironmentCheck(){return zt()?Kt().then(()=>!0).catch(()=>!1):!1}async read(){return await this._canUseIndexedDBPromise?await uo(this.app)||{heartbeats:[]}:{heartbeats:[]}}async overwrite(t){var n;if(await this._canUseIndexedDBPromise){const o=await this.read();return bt(this.app,{lastSentHeartbeatDate:(n=t.lastSentHeartbeatDate)!==null&&n!==void 0?n:o.lastSentHeartbeatDate,heartbeats:t.heartbeats})}else return}async add(t){var n;if(await this._canUseIndexedDBPromise){const o=await this.read();return bt(this.app,{lastSentHeartbeatDate:(n=t.lastSentHeartbeatDate)!==null&&n!==void 0?n:o.lastSentHeartbeatDate,heartbeats:[...o.heartbeats,...t.heartbeats]})}else return}}function wt(e){return Wt(JSON.stringify({version:2,heartbeats:e})).length}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function go(e){L(new B("platform-logger",t=>new Rr(t),"PRIVATE")),L(new B("heartbeat",t=>new ho(t),"PRIVATE")),k(Le,gt,e),k(Le,gt,"esm2017"),k("fire-js","")}go("");var bo="firebase",yo="9.21.0";/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */k(bo,yo,"app");const wo=(e,t)=>t.some(n=>e instanceof n);let Et,St;function Eo(){return Et||(Et=[IDBDatabase,IDBObjectStore,IDBIndex,IDBCursor,IDBTransaction])}function So(){return St||(St=[IDBCursor.prototype.advance,IDBCursor.prototype.continue,IDBCursor.prototype.continuePrimaryKey])}const Zt=new WeakMap,je=new WeakMap,en=new WeakMap,Ee=new WeakMap,Ye=new WeakMap;function Ao(e){const t=new Promise((n,r)=>{const o=()=>{e.removeEventListener("success",s),e.removeEventListener("error",i)},s=()=>{n(D(e.result)),o()},i=()=>{r(e.error),o()};e.addEventListener("success",s),e.addEventListener("error",i)});return t.then(n=>{n instanceof IDBCursor&&Zt.set(n,e)}).catch(()=>{}),Ye.set(t,e),t}function Io(e){if(je.has(e))return;const t=new Promise((n,r)=>{const o=()=>{e.removeEventListener("complete",s),e.removeEventListener("error",i),e.removeEventListener("abort",i)},s=()=>{n(),o()},i=()=>{r(e.error||new DOMException("AbortError","AbortError")),o()};e.addEventListener("complete",s),e.addEventListener("error",i),e.addEventListener("abort",i)});je.set(e,t)}let He={get(e,t,n){if(e instanceof IDBTransaction){if(t==="done")return je.get(e);if(t==="objectStoreNames")return e.objectStoreNames||en.get(e);if(t==="store")return n.objectStoreNames[1]?void 0:n.objectStore(n.objectStoreNames[0])}return D(e[t])},set(e,t,n){return e[t]=n,!0},has(e,t){return e instanceof IDBTransaction&&(t==="done"||t==="store")?!0:t in e}};function To(e){He=e(He)}function _o(e){return e===IDBDatabase.prototype.transaction&&!("objectStoreNames"in IDBTransaction.prototype)?function(t,...n){const r=e.call(Se(this),t,...n);return en.set(r,t.sort?t.sort():[t]),D(r)}:So().includes(e)?function(...t){return e.apply(Se(this),t),D(Zt.get(this))}:function(...t){return D(e.apply(Se(this),t))}}function Oo(e){return typeof e=="function"?_o(e):(e instanceof IDBTransaction&&Io(e),wo(e,Eo())?new Proxy(e,He):e)}function D(e){if(e instanceof IDBRequest)return Ao(e);if(Ee.has(e))return Ee.get(e);const t=Oo(e);return t!==e&&(Ee.set(e,t),Ye.set(t,e)),t}const Se=e=>Ye.get(e);function Xe(e,t,{blocked:n,upgrade:r,blocking:o,terminated:s}={}){const i=indexedDB.open(e,t),a=D(i);return r&&i.addEventListener("upgradeneeded",l=>{r(D(i.result),l.oldVersion,l.newVersion,D(i.transaction))}),n&&i.addEventListener("blocked",()=>n()),a.then(l=>{s&&l.addEventListener("close",()=>s()),o&&l.addEventListener("versionchange",()=>o())}).catch(()=>{}),a}function Ae(e,{blocked:t}={}){const n=indexedDB.deleteDatabase(e);return t&&n.addEventListener("blocked",()=>t()),D(n).then(()=>{})}const Do=["get","getKey","getAll","getAllKeys","count"],Co=["put","add","delete","clear"],Ie=new Map;function At(e,t){if(!(e instanceof IDBDatabase&&!(t in e)&&typeof t=="string"))return;if(Ie.get(t))return Ie.get(t);const n=t.replace(/FromIndex$/,""),r=t!==n,o=Co.includes(n);if(!(n in(r?IDBIndex:IDBObjectStore).prototype)||!(o||Do.includes(n)))return;const s=async function(i,...a){const l=this.transaction(i,o?"readwrite":"readonly");let c=l.store;return r&&(c=c.index(a.shift())),(await Promise.all([c[n](...a),o&&l.done]))[0]};return Ie.set(t,s),s}To(e=>({...e,get:(t,n,r)=>At(t,n)||e.get(t,n,r),has:(t,n)=>!!At(t,n)||e.has(t,n)}));const tn="@firebase/installations",Qe="0.6.4";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const nn=1e4,rn=`w:${Qe}`,on="FIS_v2",vo="https://firebaseinstallations.googleapis.com/v1",Ro=60*60*1e3,No="installations",ko="Installations";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Bo={["missing-app-config-values"]:'Missing App configuration value: "{$valueName}"',["not-registered"]:"Firebase Installation is not registered.",["installation-not-found"]:"Firebase Installation not found.",["request-failed"]:'{$requestName} request failed with error "{$serverCode} {$serverStatus}: {$serverMessage}"',["app-offline"]:"Could not process request. Application offline.",["delete-pending-registration"]:"Can't delete installation while there is a pending registration request."},F=new ae(No,ko,Bo);function sn(e){return e instanceof q&&e.code.includes("request-failed")}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function an({projectId:e}){return`${vo}/projects/${e}/installations`}function cn(e){return{token:e.token,requestStatus:2,expiresIn:Mo(e.expiresIn),creationTime:Date.now()}}async function un(e,t){const r=(await t.json()).error;return F.create("request-failed",{requestName:e,serverCode:r.code,serverMessage:r.message,serverStatus:r.status})}function ln({apiKey:e}){return new Headers({"Content-Type":"application/json",Accept:"application/json","x-goog-api-key":e})}function Po(e,{refreshToken:t}){const n=ln(e);return n.append("Authorization",xo(t)),n}async function dn(e){const t=await e();return t.status>=500&&t.status<600?e():t}function Mo(e){return Number(e.replace("s","000"))}function xo(e){return`${on} ${e}`}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function Lo({appConfig:e,heartbeatServiceProvider:t},{fid:n}){const r=an(e),o=ln(e),s=t.getImmediate({optional:!0});if(s){const c=await s.getHeartbeatsHeader();c&&o.append("x-firebase-client",c)}const i={fid:n,authVersion:on,appId:e.appId,sdkVersion:rn},a={method:"POST",headers:o,body:JSON.stringify(i)},l=await dn(()=>fetch(r,a));if(l.ok){const c=await l.json();return{fid:c.fid||n,registrationStatus:2,refreshToken:c.refreshToken,authToken:cn(c.authToken)}}else throw await un("Create Installation",l)}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function fn(e){return new Promise(t=>{setTimeout(t,e)})}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Fo(e){return btoa(String.fromCharCode(...e)).replace(/\+/g,"-").replace(/\//g,"_")}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const $o=/^[cdef][\w-]{21}$/,Ue="";function jo(){try{const e=new Uint8Array(17);(self.crypto||self.msCrypto).getRandomValues(e),e[0]=112+e[0]%16;const n=Ho(e);return $o.test(n)?n:Ue}catch{return Ue}}function Ho(e){return Fo(e).substr(0,22)}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function ce(e){return`${e.appName}!${e.appId}`}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const hn=new Map;function pn(e,t){const n=ce(e);mn(n,t),Uo(n,t)}function mn(e,t){const n=hn.get(e);if(n)for(const r of n)r(t)}function Uo(e,t){const n=Vo();n&&n.postMessage({key:e,fid:t}),qo()}let M=null;function Vo(){return!M&&"BroadcastChannel"in self&&(M=new BroadcastChannel("[Firebase] FID Change"),M.onmessage=e=>{mn(e.data.key,e.data.fid)}),M}function qo(){hn.size===0&&M&&(M.close(),M=null)}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Wo="firebase-installations-database",zo=1,$="firebase-installations-store";let Te=null;function Ze(){return Te||(Te=Xe(Wo,zo,{upgrade:(e,t)=>{switch(t){case 0:e.createObjectStore($)}}})),Te}async function oe(e,t){const n=ce(e),o=(await Ze()).transaction($,"readwrite"),s=o.objectStore($),i=await s.get(n);return await s.put(t,n),await o.done,(!i||i.fid!==t.fid)&&pn(e,t.fid),t}async function gn(e){const t=ce(e),r=(await Ze()).transaction($,"readwrite");await r.objectStore($).delete(t),await r.done}async function ue(e,t){const n=ce(e),o=(await Ze()).transaction($,"readwrite"),s=o.objectStore($),i=await s.get(n),a=t(i);return a===void 0?await s.delete(n):await s.put(a,n),await o.done,a&&(!i||i.fid!==a.fid)&&pn(e,a.fid),a}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function et(e){let t;const n=await ue(e.appConfig,r=>{const o=Ko(r),s=Jo(e,o);return t=s.registrationPromise,s.installationEntry});return n.fid===Ue?{installationEntry:await t}:{installationEntry:n,registrationPromise:t}}function Ko(e){const t=e||{fid:jo(),registrationStatus:0};return bn(t)}function Jo(e,t){if(t.registrationStatus===0){if(!navigator.onLine){const o=Promise.reject(F.create("app-offline"));return{installationEntry:t,registrationPromise:o}}const n={fid:t.fid,registrationStatus:1,registrationTime:Date.now()},r=Go(e,n);return{installationEntry:n,registrationPromise:r}}else return t.registrationStatus===1?{installationEntry:t,registrationPromise:Yo(e)}:{installationEntry:t}}async function Go(e,t){try{const n=await Lo(e,t);return oe(e.appConfig,n)}catch(n){throw sn(n)&&n.customData.serverCode===409?await gn(e.appConfig):await oe(e.appConfig,{fid:t.fid,registrationStatus:0}),n}}async function Yo(e){let t=await It(e.appConfig);for(;t.registrationStatus===1;)await fn(100),t=await It(e.appConfig);if(t.registrationStatus===0){const{installationEntry:n,registrationPromise:r}=await et(e);return r||n}return t}function It(e){return ue(e,t=>{if(!t)throw F.create("installation-not-found");return bn(t)})}function bn(e){return Xo(e)?{fid:e.fid,registrationStatus:0}:e}function Xo(e){return e.registrationStatus===1&&e.registrationTime+nn<Date.now()}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function Qo({appConfig:e,heartbeatServiceProvider:t},n){const r=Zo(e,n),o=Po(e,n),s=t.getImmediate({optional:!0});if(s){const c=await s.getHeartbeatsHeader();c&&o.append("x-firebase-client",c)}const i={installation:{sdkVersion:rn,appId:e.appId}},a={method:"POST",headers:o,body:JSON.stringify(i)},l=await dn(()=>fetch(r,a));if(l.ok){const c=await l.json();return cn(c)}else throw await un("Generate Auth Token",l)}function Zo(e,{fid:t}){return`${an(e)}/${t}/authTokens:generate`}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function tt(e,t=!1){let n;const r=await ue(e.appConfig,s=>{if(!yn(s))throw F.create("not-registered");const i=s.authToken;if(!t&&ns(i))return s;if(i.requestStatus===1)return n=es(e,t),s;{if(!navigator.onLine)throw F.create("app-offline");const a=os(s);return n=ts(e,a),a}});return n?await n:r.authToken}async function es(e,t){let n=await Tt(e.appConfig);for(;n.authToken.requestStatus===1;)await fn(100),n=await Tt(e.appConfig);const r=n.authToken;return r.requestStatus===0?tt(e,t):r}function Tt(e){return ue(e,t=>{if(!yn(t))throw F.create("not-registered");const n=t.authToken;return ss(n)?Object.assign(Object.assign({},t),{authToken:{requestStatus:0}}):t})}async function ts(e,t){try{const n=await Qo(e,t),r=Object.assign(Object.assign({},t),{authToken:n});return await oe(e.appConfig,r),n}catch(n){if(sn(n)&&(n.customData.serverCode===401||n.customData.serverCode===404))await gn(e.appConfig);else{const r=Object.assign(Object.assign({},t),{authToken:{requestStatus:0}});await oe(e.appConfig,r)}throw n}}function yn(e){return e!==void 0&&e.registrationStatus===2}function ns(e){return e.requestStatus===2&&!rs(e)}function rs(e){const t=Date.now();return t<e.creationTime||e.creationTime+e.expiresIn<t+Ro}function os(e){const t={requestStatus:1,requestTime:Date.now()};return Object.assign(Object.assign({},e),{authToken:t})}function ss(e){return e.requestStatus===1&&e.requestTime+nn<Date.now()}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function is(e){const t=e,{installationEntry:n,registrationPromise:r}=await et(t);return r?r.catch(console.error):tt(t).catch(console.error),n.fid}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function as(e,t=!1){const n=e;return await cs(n),(await tt(n,t)).token}async function cs(e){const{registrationPromise:t}=await et(e);t&&await t}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function us(e){if(!e||!e.options)throw _e("App Configuration");if(!e.name)throw _e("App Name");const t=["projectId","apiKey","appId"];for(const n of t)if(!e.options[n])throw _e(n);return{appName:e.name,projectId:e.options.projectId,apiKey:e.options.apiKey,appId:e.options.appId}}function _e(e){return F.create("missing-app-config-values",{valueName:e})}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const wn="installations",ls="installations-internal",ds=e=>{const t=e.getProvider("app").getImmediate(),n=us(t),r=Ge(t,"heartbeat");return{app:t,appConfig:n,heartbeatServiceProvider:r,_delete:()=>Promise.resolve()}},fs=e=>{const t=e.getProvider("app").getImmediate(),n=Ge(t,wn).getImmediate();return{getId:()=>is(n),getToken:o=>as(n,o)}};function hs(){L(new B(wn,ds,"PUBLIC")),L(new B(ls,fs,"PRIVATE"))}hs();k(tn,Qe);k(tn,Qe,"esm2017");/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const ps="/firebase-messaging-sw.js",ms="/firebase-cloud-messaging-push-scope",En="BDOU99-h67HcA6JeFXHbSNMu7e2yNNu3RzoMj8TM4W88jITfq7ZmPvIM1Iv-4_l2LxQcYwhqby2xGpWwzjfAnG4",gs="https://fcmregistrations.googleapis.com/v1",Sn="google.c.a.c_id",bs="google.c.a.c_l",ys="google.c.a.ts",ws="google.c.a.e";var _t;(function(e){e[e.DATA_MESSAGE=1]="DATA_MESSAGE",e[e.DISPLAY_NOTIFICATION=3]="DISPLAY_NOTIFICATION"})(_t||(_t={}));/**
 * @license
 * Copyright 2018 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License. You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under the License
 * is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
 * or implied. See the License for the specific language governing permissions and limitations under
 * the License.
 */var J;(function(e){e.PUSH_RECEIVED="push-received",e.NOTIFICATION_CLICKED="notification-clicked"})(J||(J={}));/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function O(e){const t=new Uint8Array(e);return btoa(String.fromCharCode(...t)).replace(/=/g,"").replace(/\+/g,"-").replace(/\//g,"_")}function Es(e){const t="=".repeat((4-e.length%4)%4),n=(e+t).replace(/\-/g,"+").replace(/_/g,"/"),r=atob(n),o=new Uint8Array(r.length);for(let s=0;s<r.length;++s)o[s]=r.charCodeAt(s);return o}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Oe="fcm_token_details_db",Ss=5,Ot="fcm_token_object_Store";async function As(e){if("databases"in indexedDB&&!(await indexedDB.databases()).map(s=>s.name).includes(Oe))return null;let t=null;return(await Xe(Oe,Ss,{upgrade:async(r,o,s,i)=>{var a;if(o<2||!r.objectStoreNames.contains(Ot))return;const l=i.objectStore(Ot),c=await l.index("fcmSenderId").get(e);if(await l.clear(),!!c){if(o===2){const d=c;if(!d.auth||!d.p256dh||!d.endpoint)return;t={token:d.fcmToken,createTime:(a=d.createTime)!==null&&a!==void 0?a:Date.now(),subscriptionOptions:{auth:d.auth,p256dh:d.p256dh,endpoint:d.endpoint,swScope:d.swScope,vapidKey:typeof d.vapidKey=="string"?d.vapidKey:O(d.vapidKey)}}}else if(o===3){const d=c;t={token:d.fcmToken,createTime:d.createTime,subscriptionOptions:{auth:O(d.auth),p256dh:O(d.p256dh),endpoint:d.endpoint,swScope:d.swScope,vapidKey:O(d.vapidKey)}}}else if(o===4){const d=c;t={token:d.fcmToken,createTime:d.createTime,subscriptionOptions:{auth:O(d.auth),p256dh:O(d.p256dh),endpoint:d.endpoint,swScope:d.swScope,vapidKey:O(d.vapidKey)}}}}}})).close(),await Ae(Oe),await Ae("fcm_vapid_details_db"),await Ae("undefined"),Is(t)?t:null}function Is(e){if(!e||!e.subscriptionOptions)return!1;const{subscriptionOptions:t}=e;return typeof e.createTime=="number"&&e.createTime>0&&typeof e.token=="string"&&e.token.length>0&&typeof t.auth=="string"&&t.auth.length>0&&typeof t.p256dh=="string"&&t.p256dh.length>0&&typeof t.endpoint=="string"&&t.endpoint.length>0&&typeof t.swScope=="string"&&t.swScope.length>0&&typeof t.vapidKey=="string"&&t.vapidKey.length>0}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Ts="firebase-messaging-database",_s=1,j="firebase-messaging-store";let De=null;function nt(){return De||(De=Xe(Ts,_s,{upgrade:(e,t)=>{switch(t){case 0:e.createObjectStore(j)}}})),De}async function An(e){const t=ot(e),r=await(await nt()).transaction(j).objectStore(j).get(t);if(r)return r;{const o=await As(e.appConfig.senderId);if(o)return await rt(e,o),o}}async function rt(e,t){const n=ot(e),o=(await nt()).transaction(j,"readwrite");return await o.objectStore(j).put(t,n),await o.done,t}async function Os(e){const t=ot(e),r=(await nt()).transaction(j,"readwrite");await r.objectStore(j).delete(t),await r.done}function ot({appConfig:e}){return e.appId}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Ds={["missing-app-config-values"]:'Missing App configuration value: "{$valueName}"',["only-available-in-window"]:"This method is available in a Window context.",["only-available-in-sw"]:"This method is available in a service worker context.",["permission-default"]:"The notification permission was not granted and dismissed instead.",["permission-blocked"]:"The notification permission was not granted and blocked instead.",["unsupported-browser"]:"This browser doesn't support the API's required to use the Firebase SDK.",["indexed-db-unsupported"]:"This browser doesn't support indexedDb.open() (ex. Safari iFrame, Firefox Private Browsing, etc)",["failed-service-worker-registration"]:"We are unable to register the default service worker. {$browserErrorMessage}",["token-subscribe-failed"]:"A problem occurred while subscribing the user to FCM: {$errorInfo}",["token-subscribe-no-token"]:"FCM returned no token when subscribing the user to push.",["token-unsubscribe-failed"]:"A problem occurred while unsubscribing the user from FCM: {$errorInfo}",["token-update-failed"]:"A problem occurred while updating the user from FCM: {$errorInfo}",["token-update-no-token"]:"FCM returned no token when updating the user to push.",["use-sw-after-get-token"]:"The useServiceWorker() method may only be called once and must be called before calling getToken() to ensure your service worker is used.",["invalid-sw-registration"]:"The input to useServiceWorker() must be a ServiceWorkerRegistration.",["invalid-bg-handler"]:"The input to setBackgroundMessageHandler() must be a function.",["invalid-vapid-key"]:"The public VAPID key must be a string.",["use-vapid-key-after-get-token"]:"The usePublicVapidKey() method may only be called once and must be called before calling getToken() to ensure your VAPID key is used."},E=new ae("messaging","Messaging",Ds);/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function Cs(e,t){const n=await it(e),r=Tn(t),o={method:"POST",headers:n,body:JSON.stringify(r)};let s;try{s=await(await fetch(st(e.appConfig),o)).json()}catch(i){throw E.create("token-subscribe-failed",{errorInfo:i==null?void 0:i.toString()})}if(s.error){const i=s.error.message;throw E.create("token-subscribe-failed",{errorInfo:i})}if(!s.token)throw E.create("token-subscribe-no-token");return s.token}async function vs(e,t){const n=await it(e),r=Tn(t.subscriptionOptions),o={method:"PATCH",headers:n,body:JSON.stringify(r)};let s;try{s=await(await fetch(`${st(e.appConfig)}/${t.token}`,o)).json()}catch(i){throw E.create("token-update-failed",{errorInfo:i==null?void 0:i.toString()})}if(s.error){const i=s.error.message;throw E.create("token-update-failed",{errorInfo:i})}if(!s.token)throw E.create("token-update-no-token");return s.token}async function In(e,t){const r={method:"DELETE",headers:await it(e)};try{const s=await(await fetch(`${st(e.appConfig)}/${t}`,r)).json();if(s.error){const i=s.error.message;throw E.create("token-unsubscribe-failed",{errorInfo:i})}}catch(o){throw E.create("token-unsubscribe-failed",{errorInfo:o==null?void 0:o.toString()})}}function st({projectId:e}){return`${gs}/projects/${e}/registrations`}async function it({appConfig:e,installations:t}){const n=await t.getToken();return new Headers({"Content-Type":"application/json",Accept:"application/json","x-goog-api-key":e.apiKey,"x-goog-firebase-installations-auth":`FIS ${n}`})}function Tn({p256dh:e,auth:t,endpoint:n,vapidKey:r}){const o={web:{endpoint:n,auth:t,p256dh:e}};return r!==En&&(o.web.applicationPubKey=r),o}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Rs=7*24*60*60*1e3;async function Ns(e){const t=await Ps(e.swRegistration,e.vapidKey),n={vapidKey:e.vapidKey,swScope:e.swRegistration.scope,endpoint:t.endpoint,auth:O(t.getKey("auth")),p256dh:O(t.getKey("p256dh"))},r=await An(e.firebaseDependencies);if(r){if(Ms(r.subscriptionOptions,n))return Date.now()>=r.createTime+Rs?Bs(e,{token:r.token,createTime:Date.now(),subscriptionOptions:n}):r.token;try{await In(e.firebaseDependencies,r.token)}catch(o){console.warn(o)}return Dt(e.firebaseDependencies,n)}else return Dt(e.firebaseDependencies,n)}async function ks(e){const t=await An(e.firebaseDependencies);t&&(await In(e.firebaseDependencies,t.token),await Os(e.firebaseDependencies));const n=await e.swRegistration.pushManager.getSubscription();return n?n.unsubscribe():!0}async function Bs(e,t){try{const n=await vs(e.firebaseDependencies,t),r=Object.assign(Object.assign({},t),{token:n,createTime:Date.now()});return await rt(e.firebaseDependencies,r),n}catch(n){throw await ks(e),n}}async function Dt(e,t){const r={token:await Cs(e,t),createTime:Date.now(),subscriptionOptions:t};return await rt(e,r),r.token}async function Ps(e,t){const n=await e.pushManager.getSubscription();return n||e.pushManager.subscribe({userVisibleOnly:!0,applicationServerKey:Es(t)})}function Ms(e,t){const n=t.vapidKey===e.vapidKey,r=t.endpoint===e.endpoint,o=t.auth===e.auth,s=t.p256dh===e.p256dh;return n&&r&&o&&s}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Ct(e){const t={from:e.from,collapseKey:e.collapse_key,messageId:e.fcmMessageId};return xs(t,e),Ls(t,e),Fs(t,e),t}function xs(e,t){if(!t.notification)return;e.notification={};const n=t.notification.title;n&&(e.notification.title=n);const r=t.notification.body;r&&(e.notification.body=r);const o=t.notification.image;o&&(e.notification.image=o);const s=t.notification.icon;s&&(e.notification.icon=s)}function Ls(e,t){t.data&&(e.data=t.data)}function Fs(e,t){var n,r,o,s,i;if(!t.fcmOptions&&!(!((n=t.notification)===null||n===void 0)&&n.click_action))return;e.fcmOptions={};const a=(o=(r=t.fcmOptions)===null||r===void 0?void 0:r.link)!==null&&o!==void 0?o:(s=t.notification)===null||s===void 0?void 0:s.click_action;a&&(e.fcmOptions.link=a);const l=(i=t.fcmOptions)===null||i===void 0?void 0:i.analytics_label;l&&(e.fcmOptions.analyticsLabel=l)}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function $s(e){return typeof e=="object"&&!!e&&Sn in e}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */_n("hts/frbslgigp.ogepscmv/ieo/eaylg","tp:/ieaeogn-agolai.o/1frlglgc/o");_n("AzSCbw63g1R0nCw85jG8","Iaya3yLKwmgvh7cF0q4");function _n(e,t){const n=[];for(let r=0;r<e.length;r++)n.push(e.charAt(r)),r<t.length&&n.push(t.charAt(r));return n.join("")}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function js(e){if(!e||!e.options)throw Ce("App Configuration Object");if(!e.name)throw Ce("App Name");const t=["projectId","apiKey","appId","messagingSenderId"],{options:n}=e;for(const r of t)if(!n[r])throw Ce(r);return{appName:e.name,projectId:n.projectId,apiKey:n.apiKey,appId:n.appId,senderId:n.messagingSenderId}}function Ce(e){return E.create("missing-app-config-values",{valueName:e})}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Hs{constructor(t,n,r){this.deliveryMetricsExportedToBigQueryEnabled=!1,this.onBackgroundMessageHandler=null,this.onMessageHandler=null,this.logEvents=[],this.isLogServiceStarted=!1;const o=js(t);this.firebaseDependencies={app:t,appConfig:o,installations:n,analyticsProvider:r}}_delete(){return Promise.resolve()}}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function Us(e){try{e.swRegistration=await navigator.serviceWorker.register(ps,{scope:ms}),e.swRegistration.update().catch(()=>{})}catch(t){throw E.create("failed-service-worker-registration",{browserErrorMessage:t==null?void 0:t.message})}}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function Vs(e,t){if(!t&&!e.swRegistration&&await Us(e),!(!t&&e.swRegistration)){if(!(t instanceof ServiceWorkerRegistration))throw E.create("invalid-sw-registration");e.swRegistration=t}}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function qs(e,t){t?e.vapidKey=t:e.vapidKey||(e.vapidKey=En)}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function On(e,t){if(!navigator)throw E.create("only-available-in-window");if(Notification.permission==="default"&&await Notification.requestPermission(),Notification.permission!=="granted")throw E.create("permission-blocked");return await qs(e,t==null?void 0:t.vapidKey),await Vs(e,t==null?void 0:t.serviceWorkerRegistration),Ns(e)}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function Ws(e,t,n){const r=zs(t);(await e.firebaseDependencies.analyticsProvider.get()).logEvent(r,{message_id:n[Sn],message_name:n[bs],message_time:n[ys],message_device_time:Math.floor(Date.now()/1e3)})}function zs(e){switch(e){case J.NOTIFICATION_CLICKED:return"notification_open";case J.PUSH_RECEIVED:return"notification_foreground";default:throw new Error}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function Ks(e,t){const n=t.data;if(!n.isFirebaseMessaging)return;e.onMessageHandler&&n.messageType===J.PUSH_RECEIVED&&(typeof e.onMessageHandler=="function"?e.onMessageHandler(Ct(n)):e.onMessageHandler.next(Ct(n)));const r=n.data;$s(r)&&r[ws]==="1"&&await Ws(e,n.messageType,r)}const vt="@firebase/messaging",Rt="0.12.4";/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Js=e=>{const t=new Hs(e.getProvider("app").getImmediate(),e.getProvider("installations-internal").getImmediate(),e.getProvider("analytics-internal"));return navigator.serviceWorker.addEventListener("message",n=>Ks(t,n)),t},Gs=e=>{const t=e.getProvider("messaging").getImmediate();return{getToken:r=>On(t,r)}};function Ys(){L(new B("messaging",Js,"PUBLIC")),L(new B("messaging-internal",Gs,"PRIVATE")),k(vt,Rt),k(vt,Rt,"esm2017")}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function Xs(){try{await Kt()}catch{return!1}return typeof window<"u"&&zt()&&ir()&&"serviceWorker"in navigator&&"PushManager"in window&&"Notification"in window&&"fetch"in window&&ServiceWorkerRegistration.prototype.hasOwnProperty("showNotification")&&PushSubscription.prototype.hasOwnProperty("getKey")}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Qs(e,t){if(!navigator)throw E.create("only-available-in-window");return e.onMessageHandler=t,()=>{e.onMessageHandler=null}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Zs(e=io()){return Xs().then(t=>{if(!t)throw E.create("unsupported-browser")},t=>{throw E.create("indexed-db-unsupported")}),Ge(Ke(e),"messaging").getImmediate()}async function ei(e,t){return e=Ke(e),On(e,t)}function ti(e,t){return e=Ke(e),Qs(e,t)}Ys();function Dn(e,t){return function(){return e.apply(t,arguments)}}const{toString:ni}=Object.prototype,{getPrototypeOf:at}=Object,le=(e=>t=>{const n=ni.call(t);return e[n]||(e[n]=n.slice(8,-1).toLowerCase())})(Object.create(null)),T=e=>(e=e.toLowerCase(),t=>le(t)===e),de=e=>t=>typeof t===e,{isArray:W}=Array,G=de("undefined");function ri(e){return e!==null&&!G(e)&&e.constructor!==null&&!G(e.constructor)&&S(e.constructor.isBuffer)&&e.constructor.isBuffer(e)}const Cn=T("ArrayBuffer");function oi(e){let t;return typeof ArrayBuffer<"u"&&ArrayBuffer.isView?t=ArrayBuffer.isView(e):t=e&&e.buffer&&Cn(e.buffer),t}const si=de("string"),S=de("function"),vn=de("number"),fe=e=>e!==null&&typeof e=="object",ii=e=>e===!0||e===!1,Z=e=>{if(le(e)!=="object")return!1;const t=at(e);return(t===null||t===Object.prototype||Object.getPrototypeOf(t)===null)&&!(Symbol.toStringTag in e)&&!(Symbol.iterator in e)},ai=T("Date"),ci=T("File"),ui=T("Blob"),li=T("FileList"),di=e=>fe(e)&&S(e.pipe),fi=e=>{let t;return e&&(typeof FormData=="function"&&e instanceof FormData||S(e.append)&&((t=le(e))==="formdata"||t==="object"&&S(e.toString)&&e.toString()==="[object FormData]"))},hi=T("URLSearchParams"),pi=e=>e.trim?e.trim():e.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g,"");function Y(e,t,{allOwnKeys:n=!1}={}){if(e===null||typeof e>"u")return;let r,o;if(typeof e!="object"&&(e=[e]),W(e))for(r=0,o=e.length;r<o;r++)t.call(null,e[r],r,e);else{const s=n?Object.getOwnPropertyNames(e):Object.keys(e),i=s.length;let a;for(r=0;r<i;r++)a=s[r],t.call(null,e[a],a,e)}}function Rn(e,t){t=t.toLowerCase();const n=Object.keys(e);let r=n.length,o;for(;r-- >0;)if(o=n[r],t===o.toLowerCase())return o;return null}const Nn=(()=>typeof globalThis<"u"?globalThis:typeof self<"u"?self:typeof window<"u"?window:global)(),kn=e=>!G(e)&&e!==Nn;function Ve(){const{caseless:e}=kn(this)&&this||{},t={},n=(r,o)=>{const s=e&&Rn(t,o)||o;Z(t[s])&&Z(r)?t[s]=Ve(t[s],r):Z(r)?t[s]=Ve({},r):W(r)?t[s]=r.slice():t[s]=r};for(let r=0,o=arguments.length;r<o;r++)arguments[r]&&Y(arguments[r],n);return t}const mi=(e,t,n,{allOwnKeys:r}={})=>(Y(t,(o,s)=>{n&&S(o)?e[s]=Dn(o,n):e[s]=o},{allOwnKeys:r}),e),gi=e=>(e.charCodeAt(0)===65279&&(e=e.slice(1)),e),bi=(e,t,n,r)=>{e.prototype=Object.create(t.prototype,r),e.prototype.constructor=e,Object.defineProperty(e,"super",{value:t.prototype}),n&&Object.assign(e.prototype,n)},yi=(e,t,n,r)=>{let o,s,i;const a={};if(t=t||{},e==null)return t;do{for(o=Object.getOwnPropertyNames(e),s=o.length;s-- >0;)i=o[s],(!r||r(i,e,t))&&!a[i]&&(t[i]=e[i],a[i]=!0);e=n!==!1&&at(e)}while(e&&(!n||n(e,t))&&e!==Object.prototype);return t},wi=(e,t,n)=>{e=String(e),(n===void 0||n>e.length)&&(n=e.length),n-=t.length;const r=e.indexOf(t,n);return r!==-1&&r===n},Ei=e=>{if(!e)return null;if(W(e))return e;let t=e.length;if(!vn(t))return null;const n=new Array(t);for(;t-- >0;)n[t]=e[t];return n},Si=(e=>t=>e&&t instanceof e)(typeof Uint8Array<"u"&&at(Uint8Array)),Ai=(e,t)=>{const r=(e&&e[Symbol.iterator]).call(e);let o;for(;(o=r.next())&&!o.done;){const s=o.value;t.call(e,s[0],s[1])}},Ii=(e,t)=>{let n;const r=[];for(;(n=e.exec(t))!==null;)r.push(n);return r},Ti=T("HTMLFormElement"),_i=e=>e.toLowerCase().replace(/[-_\s]([a-z\d])(\w*)/g,function(n,r,o){return r.toUpperCase()+o}),Nt=(({hasOwnProperty:e})=>(t,n)=>e.call(t,n))(Object.prototype),Oi=T("RegExp"),Bn=(e,t)=>{const n=Object.getOwnPropertyDescriptors(e),r={};Y(n,(o,s)=>{t(o,s,e)!==!1&&(r[s]=o)}),Object.defineProperties(e,r)},Di=e=>{Bn(e,(t,n)=>{if(S(e)&&["arguments","caller","callee"].indexOf(n)!==-1)return!1;const r=e[n];if(S(r)){if(t.enumerable=!1,"writable"in t){t.writable=!1;return}t.set||(t.set=()=>{throw Error("Can not rewrite read-only method '"+n+"'")})}})},Ci=(e,t)=>{const n={},r=o=>{o.forEach(s=>{n[s]=!0})};return W(e)?r(e):r(String(e).split(t)),n},vi=()=>{},Ri=(e,t)=>(e=+e,Number.isFinite(e)?e:t),ve="abcdefghijklmnopqrstuvwxyz",kt="0123456789",Pn={DIGIT:kt,ALPHA:ve,ALPHA_DIGIT:ve+ve.toUpperCase()+kt},Ni=(e=16,t=Pn.ALPHA_DIGIT)=>{let n="";const{length:r}=t;for(;e--;)n+=t[Math.random()*r|0];return n};function ki(e){return!!(e&&S(e.append)&&e[Symbol.toStringTag]==="FormData"&&e[Symbol.iterator])}const Bi=e=>{const t=new Array(10),n=(r,o)=>{if(fe(r)){if(t.indexOf(r)>=0)return;if(!("toJSON"in r)){t[o]=r;const s=W(r)?[]:{};return Y(r,(i,a)=>{const l=n(i,o+1);!G(l)&&(s[a]=l)}),t[o]=void 0,s}}return r};return n(e,0)},Pi=T("AsyncFunction"),Mi=e=>e&&(fe(e)||S(e))&&S(e.then)&&S(e.catch),u={isArray:W,isArrayBuffer:Cn,isBuffer:ri,isFormData:fi,isArrayBufferView:oi,isString:si,isNumber:vn,isBoolean:ii,isObject:fe,isPlainObject:Z,isUndefined:G,isDate:ai,isFile:ci,isBlob:ui,isRegExp:Oi,isFunction:S,isStream:di,isURLSearchParams:hi,isTypedArray:Si,isFileList:li,forEach:Y,merge:Ve,extend:mi,trim:pi,stripBOM:gi,inherits:bi,toFlatObject:yi,kindOf:le,kindOfTest:T,endsWith:wi,toArray:Ei,forEachEntry:Ai,matchAll:Ii,isHTMLForm:Ti,hasOwnProperty:Nt,hasOwnProp:Nt,reduceDescriptors:Bn,freezeMethods:Di,toObjectSet:Ci,toCamelCase:_i,noop:vi,toFiniteNumber:Ri,findKey:Rn,global:Nn,isContextDefined:kn,ALPHABET:Pn,generateString:Ni,isSpecCompliantForm:ki,toJSONObject:Bi,isAsyncFn:Pi,isThenable:Mi};function m(e,t,n,r,o){Error.call(this),Error.captureStackTrace?Error.captureStackTrace(this,this.constructor):this.stack=new Error().stack,this.message=e,this.name="AxiosError",t&&(this.code=t),n&&(this.config=n),r&&(this.request=r),o&&(this.response=o)}u.inherits(m,Error,{toJSON:function(){return{message:this.message,name:this.name,description:this.description,number:this.number,fileName:this.fileName,lineNumber:this.lineNumber,columnNumber:this.columnNumber,stack:this.stack,config:u.toJSONObject(this.config),code:this.code,status:this.response&&this.response.status?this.response.status:null}}});const Mn=m.prototype,xn={};["ERR_BAD_OPTION_VALUE","ERR_BAD_OPTION","ECONNABORTED","ETIMEDOUT","ERR_NETWORK","ERR_FR_TOO_MANY_REDIRECTS","ERR_DEPRECATED","ERR_BAD_RESPONSE","ERR_BAD_REQUEST","ERR_CANCELED","ERR_NOT_SUPPORT","ERR_INVALID_URL"].forEach(e=>{xn[e]={value:e}});Object.defineProperties(m,xn);Object.defineProperty(Mn,"isAxiosError",{value:!0});m.from=(e,t,n,r,o,s)=>{const i=Object.create(Mn);return u.toFlatObject(e,i,function(l){return l!==Error.prototype},a=>a!=="isAxiosError"),m.call(i,e.message,t,n,r,o),i.cause=e,i.name=e.name,s&&Object.assign(i,s),i};const xi=null;function qe(e){return u.isPlainObject(e)||u.isArray(e)}function Ln(e){return u.endsWith(e,"[]")?e.slice(0,-2):e}function Bt(e,t,n){return e?e.concat(t).map(function(o,s){return o=Ln(o),!n&&s?"["+o+"]":o}).join(n?".":""):t}function Li(e){return u.isArray(e)&&!e.some(qe)}const Fi=u.toFlatObject(u,{},null,function(t){return/^is[A-Z]/.test(t)});function he(e,t,n){if(!u.isObject(e))throw new TypeError("target must be an object");t=t||new FormData,n=u.toFlatObject(n,{metaTokens:!0,dots:!1,indexes:!1},!1,function(p,_){return!u.isUndefined(_[p])});const r=n.metaTokens,o=n.visitor||d,s=n.dots,i=n.indexes,l=(n.Blob||typeof Blob<"u"&&Blob)&&u.isSpecCompliantForm(t);if(!u.isFunction(o))throw new TypeError("visitor must be a function");function c(f){if(f===null)return"";if(u.isDate(f))return f.toISOString();if(!l&&u.isBlob(f))throw new m("Blob is not supported. Use a Buffer instead.");return u.isArrayBuffer(f)||u.isTypedArray(f)?l&&typeof Blob=="function"?new Blob([f]):Buffer.from(f):f}function d(f,p,_){let A=f;if(f&&!_&&typeof f=="object"){if(u.endsWith(p,"{}"))p=r?p:p.slice(0,-2),f=JSON.stringify(f);else if(u.isArray(f)&&Li(f)||(u.isFileList(f)||u.endsWith(p,"[]"))&&(A=u.toArray(f)))return p=Ln(p),A.forEach(function(Q,Jn){!(u.isUndefined(Q)||Q===null)&&t.append(i===!0?Bt([p],Jn,s):i===null?p:p+"[]",c(Q))}),!1}return qe(f)?!0:(t.append(Bt(_,p,s),c(f)),!1)}const h=[],y=Object.assign(Fi,{defaultVisitor:d,convertValue:c,isVisitable:qe});function g(f,p){if(!u.isUndefined(f)){if(h.indexOf(f)!==-1)throw Error("Circular reference detected in "+p.join("."));h.push(f),u.forEach(f,function(A,H){(!(u.isUndefined(A)||A===null)&&o.call(t,A,u.isString(H)?H.trim():H,p,y))===!0&&g(A,p?p.concat(H):[H])}),h.pop()}}if(!u.isObject(e))throw new TypeError("data must be an object");return g(e),t}function Pt(e){const t={"!":"%21","'":"%27","(":"%28",")":"%29","~":"%7E","%20":"+","%00":"\0"};return encodeURIComponent(e).replace(/[!'()~]|%20|%00/g,function(r){return t[r]})}function ct(e,t){this._pairs=[],e&&he(e,this,t)}const Fn=ct.prototype;Fn.append=function(t,n){this._pairs.push([t,n])};Fn.toString=function(t){const n=t?function(r){return t.call(this,r,Pt)}:Pt;return this._pairs.map(function(o){return n(o[0])+"="+n(o[1])},"").join("&")};function $i(e){return encodeURIComponent(e).replace(/%3A/gi,":").replace(/%24/g,"$").replace(/%2C/gi,",").replace(/%20/g,"+").replace(/%5B/gi,"[").replace(/%5D/gi,"]")}function $n(e,t,n){if(!t)return e;const r=n&&n.encode||$i,o=n&&n.serialize;let s;if(o?s=o(t,n):s=u.isURLSearchParams(t)?t.toString():new ct(t,n).toString(r),s){const i=e.indexOf("#");i!==-1&&(e=e.slice(0,i)),e+=(e.indexOf("?")===-1?"?":"&")+s}return e}class ji{constructor(){this.handlers=[]}use(t,n,r){return this.handlers.push({fulfilled:t,rejected:n,synchronous:r?r.synchronous:!1,runWhen:r?r.runWhen:null}),this.handlers.length-1}eject(t){this.handlers[t]&&(this.handlers[t]=null)}clear(){this.handlers&&(this.handlers=[])}forEach(t){u.forEach(this.handlers,function(r){r!==null&&t(r)})}}const Mt=ji,jn={silentJSONParsing:!0,forcedJSONParsing:!0,clarifyTimeoutError:!1},Hi=typeof URLSearchParams<"u"?URLSearchParams:ct,Ui=typeof FormData<"u"?FormData:null,Vi=typeof Blob<"u"?Blob:null,qi=(()=>{let e;return typeof navigator<"u"&&((e=navigator.product)==="ReactNative"||e==="NativeScript"||e==="NS")?!1:typeof window<"u"&&typeof document<"u"})(),Wi=(()=>typeof WorkerGlobalScope<"u"&&self instanceof WorkerGlobalScope&&typeof self.importScripts=="function")(),I={isBrowser:!0,classes:{URLSearchParams:Hi,FormData:Ui,Blob:Vi},isStandardBrowserEnv:qi,isStandardBrowserWebWorkerEnv:Wi,protocols:["http","https","file","blob","url","data"]};function zi(e,t){return he(e,new I.classes.URLSearchParams,Object.assign({visitor:function(n,r,o,s){return I.isNode&&u.isBuffer(n)?(this.append(r,n.toString("base64")),!1):s.defaultVisitor.apply(this,arguments)}},t))}function Ki(e){return u.matchAll(/\w+|\[(\w*)]/g,e).map(t=>t[0]==="[]"?"":t[1]||t[0])}function Ji(e){const t={},n=Object.keys(e);let r;const o=n.length;let s;for(r=0;r<o;r++)s=n[r],t[s]=e[s];return t}function Hn(e){function t(n,r,o,s){let i=n[s++];const a=Number.isFinite(+i),l=s>=n.length;return i=!i&&u.isArray(o)?o.length:i,l?(u.hasOwnProp(o,i)?o[i]=[o[i],r]:o[i]=r,!a):((!o[i]||!u.isObject(o[i]))&&(o[i]=[]),t(n,r,o[i],s)&&u.isArray(o[i])&&(o[i]=Ji(o[i])),!a)}if(u.isFormData(e)&&u.isFunction(e.entries)){const n={};return u.forEachEntry(e,(r,o)=>{t(Ki(r),o,n,0)}),n}return null}const Gi={"Content-Type":void 0};function Yi(e,t,n){if(u.isString(e))try{return(t||JSON.parse)(e),u.trim(e)}catch(r){if(r.name!=="SyntaxError")throw r}return(n||JSON.stringify)(e)}const pe={transitional:jn,adapter:["xhr","http"],transformRequest:[function(t,n){const r=n.getContentType()||"",o=r.indexOf("application/json")>-1,s=u.isObject(t);if(s&&u.isHTMLForm(t)&&(t=new FormData(t)),u.isFormData(t))return o&&o?JSON.stringify(Hn(t)):t;if(u.isArrayBuffer(t)||u.isBuffer(t)||u.isStream(t)||u.isFile(t)||u.isBlob(t))return t;if(u.isArrayBufferView(t))return t.buffer;if(u.isURLSearchParams(t))return n.setContentType("application/x-www-form-urlencoded;charset=utf-8",!1),t.toString();let a;if(s){if(r.indexOf("application/x-www-form-urlencoded")>-1)return zi(t,this.formSerializer).toString();if((a=u.isFileList(t))||r.indexOf("multipart/form-data")>-1){const l=this.env&&this.env.FormData;return he(a?{"files[]":t}:t,l&&new l,this.formSerializer)}}return s||o?(n.setContentType("application/json",!1),Yi(t)):t}],transformResponse:[function(t){const n=this.transitional||pe.transitional,r=n&&n.forcedJSONParsing,o=this.responseType==="json";if(t&&u.isString(t)&&(r&&!this.responseType||o)){const i=!(n&&n.silentJSONParsing)&&o;try{return JSON.parse(t)}catch(a){if(i)throw a.name==="SyntaxError"?m.from(a,m.ERR_BAD_RESPONSE,this,null,this.response):a}}return t}],timeout:0,xsrfCookieName:"XSRF-TOKEN",xsrfHeaderName:"X-XSRF-TOKEN",maxContentLength:-1,maxBodyLength:-1,env:{FormData:I.classes.FormData,Blob:I.classes.Blob},validateStatus:function(t){return t>=200&&t<300},headers:{common:{Accept:"application/json, text/plain, */*"}}};u.forEach(["delete","get","head"],function(t){pe.headers[t]={}});u.forEach(["post","put","patch"],function(t){pe.headers[t]=u.merge(Gi)});const ut=pe,Xi=u.toObjectSet(["age","authorization","content-length","content-type","etag","expires","from","host","if-modified-since","if-unmodified-since","last-modified","location","max-forwards","proxy-authorization","referer","retry-after","user-agent"]),Qi=e=>{const t={};let n,r,o;return e&&e.split(`
`).forEach(function(i){o=i.indexOf(":"),n=i.substring(0,o).trim().toLowerCase(),r=i.substring(o+1).trim(),!(!n||t[n]&&Xi[n])&&(n==="set-cookie"?t[n]?t[n].push(r):t[n]=[r]:t[n]=t[n]?t[n]+", "+r:r)}),t},xt=Symbol("internals");function z(e){return e&&String(e).trim().toLowerCase()}function ee(e){return e===!1||e==null?e:u.isArray(e)?e.map(ee):String(e)}function Zi(e){const t=Object.create(null),n=/([^\s,;=]+)\s*(?:=\s*([^,;]+))?/g;let r;for(;r=n.exec(e);)t[r[1]]=r[2];return t}const ea=e=>/^[-_a-zA-Z0-9^`|~,!#$%&'*+.]+$/.test(e.trim());function Re(e,t,n,r,o){if(u.isFunction(r))return r.call(this,t,n);if(o&&(t=n),!!u.isString(t)){if(u.isString(r))return t.indexOf(r)!==-1;if(u.isRegExp(r))return r.test(t)}}function ta(e){return e.trim().toLowerCase().replace(/([a-z\d])(\w*)/g,(t,n,r)=>n.toUpperCase()+r)}function na(e,t){const n=u.toCamelCase(" "+t);["get","set","has"].forEach(r=>{Object.defineProperty(e,r+n,{value:function(o,s,i){return this[r].call(this,t,o,s,i)},configurable:!0})})}class me{constructor(t){t&&this.set(t)}set(t,n,r){const o=this;function s(a,l,c){const d=z(l);if(!d)throw new Error("header name must be a non-empty string");const h=u.findKey(o,d);(!h||o[h]===void 0||c===!0||c===void 0&&o[h]!==!1)&&(o[h||l]=ee(a))}const i=(a,l)=>u.forEach(a,(c,d)=>s(c,d,l));return u.isPlainObject(t)||t instanceof this.constructor?i(t,n):u.isString(t)&&(t=t.trim())&&!ea(t)?i(Qi(t),n):t!=null&&s(n,t,r),this}get(t,n){if(t=z(t),t){const r=u.findKey(this,t);if(r){const o=this[r];if(!n)return o;if(n===!0)return Zi(o);if(u.isFunction(n))return n.call(this,o,r);if(u.isRegExp(n))return n.exec(o);throw new TypeError("parser must be boolean|regexp|function")}}}has(t,n){if(t=z(t),t){const r=u.findKey(this,t);return!!(r&&this[r]!==void 0&&(!n||Re(this,this[r],r,n)))}return!1}delete(t,n){const r=this;let o=!1;function s(i){if(i=z(i),i){const a=u.findKey(r,i);a&&(!n||Re(r,r[a],a,n))&&(delete r[a],o=!0)}}return u.isArray(t)?t.forEach(s):s(t),o}clear(t){const n=Object.keys(this);let r=n.length,o=!1;for(;r--;){const s=n[r];(!t||Re(this,this[s],s,t,!0))&&(delete this[s],o=!0)}return o}normalize(t){const n=this,r={};return u.forEach(this,(o,s)=>{const i=u.findKey(r,s);if(i){n[i]=ee(o),delete n[s];return}const a=t?ta(s):String(s).trim();a!==s&&delete n[s],n[a]=ee(o),r[a]=!0}),this}concat(...t){return this.constructor.concat(this,...t)}toJSON(t){const n=Object.create(null);return u.forEach(this,(r,o)=>{r!=null&&r!==!1&&(n[o]=t&&u.isArray(r)?r.join(", "):r)}),n}[Symbol.iterator](){return Object.entries(this.toJSON())[Symbol.iterator]()}toString(){return Object.entries(this.toJSON()).map(([t,n])=>t+": "+n).join(`
`)}get[Symbol.toStringTag](){return"AxiosHeaders"}static from(t){return t instanceof this?t:new this(t)}static concat(t,...n){const r=new this(t);return n.forEach(o=>r.set(o)),r}static accessor(t){const r=(this[xt]=this[xt]={accessors:{}}).accessors,o=this.prototype;function s(i){const a=z(i);r[a]||(na(o,i),r[a]=!0)}return u.isArray(t)?t.forEach(s):s(t),this}}me.accessor(["Content-Type","Content-Length","Accept","Accept-Encoding","User-Agent","Authorization"]);u.freezeMethods(me.prototype);u.freezeMethods(me);const C=me;function Ne(e,t){const n=this||ut,r=t||n,o=C.from(r.headers);let s=r.data;return u.forEach(e,function(a){s=a.call(n,s,o.normalize(),t?t.status:void 0)}),o.normalize(),s}function Un(e){return!!(e&&e.__CANCEL__)}function X(e,t,n){m.call(this,e??"canceled",m.ERR_CANCELED,t,n),this.name="CanceledError"}u.inherits(X,m,{__CANCEL__:!0});function ra(e,t,n){const r=n.config.validateStatus;!n.status||!r||r(n.status)?e(n):t(new m("Request failed with status code "+n.status,[m.ERR_BAD_REQUEST,m.ERR_BAD_RESPONSE][Math.floor(n.status/100)-4],n.config,n.request,n))}const oa=I.isStandardBrowserEnv?function(){return{write:function(n,r,o,s,i,a){const l=[];l.push(n+"="+encodeURIComponent(r)),u.isNumber(o)&&l.push("expires="+new Date(o).toGMTString()),u.isString(s)&&l.push("path="+s),u.isString(i)&&l.push("domain="+i),a===!0&&l.push("secure"),document.cookie=l.join("; ")},read:function(n){const r=document.cookie.match(new RegExp("(^|;\\s*)("+n+")=([^;]*)"));return r?decodeURIComponent(r[3]):null},remove:function(n){this.write(n,"",Date.now()-864e5)}}}():function(){return{write:function(){},read:function(){return null},remove:function(){}}}();function sa(e){return/^([a-z][a-z\d+\-.]*:)?\/\//i.test(e)}function ia(e,t){return t?e.replace(/\/+$/,"")+"/"+t.replace(/^\/+/,""):e}function Vn(e,t){return e&&!sa(t)?ia(e,t):t}const aa=I.isStandardBrowserEnv?function(){const t=/(msie|trident)/i.test(navigator.userAgent),n=document.createElement("a");let r;function o(s){let i=s;return t&&(n.setAttribute("href",i),i=n.href),n.setAttribute("href",i),{href:n.href,protocol:n.protocol?n.protocol.replace(/:$/,""):"",host:n.host,search:n.search?n.search.replace(/^\?/,""):"",hash:n.hash?n.hash.replace(/^#/,""):"",hostname:n.hostname,port:n.port,pathname:n.pathname.charAt(0)==="/"?n.pathname:"/"+n.pathname}}return r=o(window.location.href),function(i){const a=u.isString(i)?o(i):i;return a.protocol===r.protocol&&a.host===r.host}}():function(){return function(){return!0}}();function ca(e){const t=/^([-+\w]{1,25})(:?\/\/|:)/.exec(e);return t&&t[1]||""}function ua(e,t){e=e||10;const n=new Array(e),r=new Array(e);let o=0,s=0,i;return t=t!==void 0?t:1e3,function(l){const c=Date.now(),d=r[s];i||(i=c),n[o]=l,r[o]=c;let h=s,y=0;for(;h!==o;)y+=n[h++],h=h%e;if(o=(o+1)%e,o===s&&(s=(s+1)%e),c-i<t)return;const g=d&&c-d;return g?Math.round(y*1e3/g):void 0}}function Lt(e,t){let n=0;const r=ua(50,250);return o=>{const s=o.loaded,i=o.lengthComputable?o.total:void 0,a=s-n,l=r(a),c=s<=i;n=s;const d={loaded:s,total:i,progress:i?s/i:void 0,bytes:a,rate:l||void 0,estimated:l&&i&&c?(i-s)/l:void 0,event:o};d[t?"download":"upload"]=!0,e(d)}}const la=typeof XMLHttpRequest<"u",da=la&&function(e){return new Promise(function(n,r){let o=e.data;const s=C.from(e.headers).normalize(),i=e.responseType;let a;function l(){e.cancelToken&&e.cancelToken.unsubscribe(a),e.signal&&e.signal.removeEventListener("abort",a)}u.isFormData(o)&&(I.isStandardBrowserEnv||I.isStandardBrowserWebWorkerEnv?s.setContentType(!1):s.setContentType("multipart/form-data;",!1));let c=new XMLHttpRequest;if(e.auth){const g=e.auth.username||"",f=e.auth.password?unescape(encodeURIComponent(e.auth.password)):"";s.set("Authorization","Basic "+btoa(g+":"+f))}const d=Vn(e.baseURL,e.url);c.open(e.method.toUpperCase(),$n(d,e.params,e.paramsSerializer),!0),c.timeout=e.timeout;function h(){if(!c)return;const g=C.from("getAllResponseHeaders"in c&&c.getAllResponseHeaders()),p={data:!i||i==="text"||i==="json"?c.responseText:c.response,status:c.status,statusText:c.statusText,headers:g,config:e,request:c};ra(function(A){n(A),l()},function(A){r(A),l()},p),c=null}if("onloadend"in c?c.onloadend=h:c.onreadystatechange=function(){!c||c.readyState!==4||c.status===0&&!(c.responseURL&&c.responseURL.indexOf("file:")===0)||setTimeout(h)},c.onabort=function(){c&&(r(new m("Request aborted",m.ECONNABORTED,e,c)),c=null)},c.onerror=function(){r(new m("Network Error",m.ERR_NETWORK,e,c)),c=null},c.ontimeout=function(){let f=e.timeout?"timeout of "+e.timeout+"ms exceeded":"timeout exceeded";const p=e.transitional||jn;e.timeoutErrorMessage&&(f=e.timeoutErrorMessage),r(new m(f,p.clarifyTimeoutError?m.ETIMEDOUT:m.ECONNABORTED,e,c)),c=null},I.isStandardBrowserEnv){const g=(e.withCredentials||aa(d))&&e.xsrfCookieName&&oa.read(e.xsrfCookieName);g&&s.set(e.xsrfHeaderName,g)}o===void 0&&s.setContentType(null),"setRequestHeader"in c&&u.forEach(s.toJSON(),function(f,p){c.setRequestHeader(p,f)}),u.isUndefined(e.withCredentials)||(c.withCredentials=!!e.withCredentials),i&&i!=="json"&&(c.responseType=e.responseType),typeof e.onDownloadProgress=="function"&&c.addEventListener("progress",Lt(e.onDownloadProgress,!0)),typeof e.onUploadProgress=="function"&&c.upload&&c.upload.addEventListener("progress",Lt(e.onUploadProgress)),(e.cancelToken||e.signal)&&(a=g=>{c&&(r(!g||g.type?new X(null,e,c):g),c.abort(),c=null)},e.cancelToken&&e.cancelToken.subscribe(a),e.signal&&(e.signal.aborted?a():e.signal.addEventListener("abort",a)));const y=ca(d);if(y&&I.protocols.indexOf(y)===-1){r(new m("Unsupported protocol "+y+":",m.ERR_BAD_REQUEST,e));return}c.send(o||null)})},te={http:xi,xhr:da};u.forEach(te,(e,t)=>{if(e){try{Object.defineProperty(e,"name",{value:t})}catch{}Object.defineProperty(e,"adapterName",{value:t})}});const fa={getAdapter:e=>{e=u.isArray(e)?e:[e];const{length:t}=e;let n,r;for(let o=0;o<t&&(n=e[o],!(r=u.isString(n)?te[n.toLowerCase()]:n));o++);if(!r)throw r===!1?new m(`Adapter ${n} is not supported by the environment`,"ERR_NOT_SUPPORT"):new Error(u.hasOwnProp(te,n)?`Adapter '${n}' is not available in the build`:`Unknown adapter '${n}'`);if(!u.isFunction(r))throw new TypeError("adapter is not a function");return r},adapters:te};function ke(e){if(e.cancelToken&&e.cancelToken.throwIfRequested(),e.signal&&e.signal.aborted)throw new X(null,e)}function Ft(e){return ke(e),e.headers=C.from(e.headers),e.data=Ne.call(e,e.transformRequest),["post","put","patch"].indexOf(e.method)!==-1&&e.headers.setContentType("application/x-www-form-urlencoded",!1),fa.getAdapter(e.adapter||ut.adapter)(e).then(function(r){return ke(e),r.data=Ne.call(e,e.transformResponse,r),r.headers=C.from(r.headers),r},function(r){return Un(r)||(ke(e),r&&r.response&&(r.response.data=Ne.call(e,e.transformResponse,r.response),r.response.headers=C.from(r.response.headers))),Promise.reject(r)})}const $t=e=>e instanceof C?e.toJSON():e;function V(e,t){t=t||{};const n={};function r(c,d,h){return u.isPlainObject(c)&&u.isPlainObject(d)?u.merge.call({caseless:h},c,d):u.isPlainObject(d)?u.merge({},d):u.isArray(d)?d.slice():d}function o(c,d,h){if(u.isUndefined(d)){if(!u.isUndefined(c))return r(void 0,c,h)}else return r(c,d,h)}function s(c,d){if(!u.isUndefined(d))return r(void 0,d)}function i(c,d){if(u.isUndefined(d)){if(!u.isUndefined(c))return r(void 0,c)}else return r(void 0,d)}function a(c,d,h){if(h in t)return r(c,d);if(h in e)return r(void 0,c)}const l={url:s,method:s,data:s,baseURL:i,transformRequest:i,transformResponse:i,paramsSerializer:i,timeout:i,timeoutMessage:i,withCredentials:i,adapter:i,responseType:i,xsrfCookieName:i,xsrfHeaderName:i,onUploadProgress:i,onDownloadProgress:i,decompress:i,maxContentLength:i,maxBodyLength:i,beforeRedirect:i,transport:i,httpAgent:i,httpsAgent:i,cancelToken:i,socketPath:i,responseEncoding:i,validateStatus:a,headers:(c,d)=>o($t(c),$t(d),!0)};return u.forEach(Object.keys(Object.assign({},e,t)),function(d){const h=l[d]||o,y=h(e[d],t[d],d);u.isUndefined(y)&&h!==a||(n[d]=y)}),n}const qn="1.4.0",lt={};["object","boolean","number","function","string","symbol"].forEach((e,t)=>{lt[e]=function(r){return typeof r===e||"a"+(t<1?"n ":" ")+e}});const jt={};lt.transitional=function(t,n,r){function o(s,i){return"[Axios v"+qn+"] Transitional option '"+s+"'"+i+(r?". "+r:"")}return(s,i,a)=>{if(t===!1)throw new m(o(i," has been removed"+(n?" in "+n:"")),m.ERR_DEPRECATED);return n&&!jt[i]&&(jt[i]=!0,console.warn(o(i," has been deprecated since v"+n+" and will be removed in the near future"))),t?t(s,i,a):!0}};function ha(e,t,n){if(typeof e!="object")throw new m("options must be an object",m.ERR_BAD_OPTION_VALUE);const r=Object.keys(e);let o=r.length;for(;o-- >0;){const s=r[o],i=t[s];if(i){const a=e[s],l=a===void 0||i(a,s,e);if(l!==!0)throw new m("option "+s+" must be "+l,m.ERR_BAD_OPTION_VALUE);continue}if(n!==!0)throw new m("Unknown option "+s,m.ERR_BAD_OPTION)}}const We={assertOptions:ha,validators:lt},v=We.validators;class se{constructor(t){this.defaults=t,this.interceptors={request:new Mt,response:new Mt}}request(t,n){typeof t=="string"?(n=n||{},n.url=t):n=t||{},n=V(this.defaults,n);const{transitional:r,paramsSerializer:o,headers:s}=n;r!==void 0&&We.assertOptions(r,{silentJSONParsing:v.transitional(v.boolean),forcedJSONParsing:v.transitional(v.boolean),clarifyTimeoutError:v.transitional(v.boolean)},!1),o!=null&&(u.isFunction(o)?n.paramsSerializer={serialize:o}:We.assertOptions(o,{encode:v.function,serialize:v.function},!0)),n.method=(n.method||this.defaults.method||"get").toLowerCase();let i;i=s&&u.merge(s.common,s[n.method]),i&&u.forEach(["delete","get","head","post","put","patch","common"],f=>{delete s[f]}),n.headers=C.concat(i,s);const a=[];let l=!0;this.interceptors.request.forEach(function(p){typeof p.runWhen=="function"&&p.runWhen(n)===!1||(l=l&&p.synchronous,a.unshift(p.fulfilled,p.rejected))});const c=[];this.interceptors.response.forEach(function(p){c.push(p.fulfilled,p.rejected)});let d,h=0,y;if(!l){const f=[Ft.bind(this),void 0];for(f.unshift.apply(f,a),f.push.apply(f,c),y=f.length,d=Promise.resolve(n);h<y;)d=d.then(f[h++],f[h++]);return d}y=a.length;let g=n;for(h=0;h<y;){const f=a[h++],p=a[h++];try{g=f(g)}catch(_){p.call(this,_);break}}try{d=Ft.call(this,g)}catch(f){return Promise.reject(f)}for(h=0,y=c.length;h<y;)d=d.then(c[h++],c[h++]);return d}getUri(t){t=V(this.defaults,t);const n=Vn(t.baseURL,t.url);return $n(n,t.params,t.paramsSerializer)}}u.forEach(["delete","get","head","options"],function(t){se.prototype[t]=function(n,r){return this.request(V(r||{},{method:t,url:n,data:(r||{}).data}))}});u.forEach(["post","put","patch"],function(t){function n(r){return function(s,i,a){return this.request(V(a||{},{method:t,headers:r?{"Content-Type":"multipart/form-data"}:{},url:s,data:i}))}}se.prototype[t]=n(),se.prototype[t+"Form"]=n(!0)});const ne=se;class dt{constructor(t){if(typeof t!="function")throw new TypeError("executor must be a function.");let n;this.promise=new Promise(function(s){n=s});const r=this;this.promise.then(o=>{if(!r._listeners)return;let s=r._listeners.length;for(;s-- >0;)r._listeners[s](o);r._listeners=null}),this.promise.then=o=>{let s;const i=new Promise(a=>{r.subscribe(a),s=a}).then(o);return i.cancel=function(){r.unsubscribe(s)},i},t(function(s,i,a){r.reason||(r.reason=new X(s,i,a),n(r.reason))})}throwIfRequested(){if(this.reason)throw this.reason}subscribe(t){if(this.reason){t(this.reason);return}this._listeners?this._listeners.push(t):this._listeners=[t]}unsubscribe(t){if(!this._listeners)return;const n=this._listeners.indexOf(t);n!==-1&&this._listeners.splice(n,1)}static source(){let t;return{token:new dt(function(o){t=o}),cancel:t}}}const pa=dt;function ma(e){return function(n){return e.apply(null,n)}}function ga(e){return u.isObject(e)&&e.isAxiosError===!0}const ze={Continue:100,SwitchingProtocols:101,Processing:102,EarlyHints:103,Ok:200,Created:201,Accepted:202,NonAuthoritativeInformation:203,NoContent:204,ResetContent:205,PartialContent:206,MultiStatus:207,AlreadyReported:208,ImUsed:226,MultipleChoices:300,MovedPermanently:301,Found:302,SeeOther:303,NotModified:304,UseProxy:305,Unused:306,TemporaryRedirect:307,PermanentRedirect:308,BadRequest:400,Unauthorized:401,PaymentRequired:402,Forbidden:403,NotFound:404,MethodNotAllowed:405,NotAcceptable:406,ProxyAuthenticationRequired:407,RequestTimeout:408,Conflict:409,Gone:410,LengthRequired:411,PreconditionFailed:412,PayloadTooLarge:413,UriTooLong:414,UnsupportedMediaType:415,RangeNotSatisfiable:416,ExpectationFailed:417,ImATeapot:418,MisdirectedRequest:421,UnprocessableEntity:422,Locked:423,FailedDependency:424,TooEarly:425,UpgradeRequired:426,PreconditionRequired:428,TooManyRequests:429,RequestHeaderFieldsTooLarge:431,UnavailableForLegalReasons:451,InternalServerError:500,NotImplemented:501,BadGateway:502,ServiceUnavailable:503,GatewayTimeout:504,HttpVersionNotSupported:505,VariantAlsoNegotiates:506,InsufficientStorage:507,LoopDetected:508,NotExtended:510,NetworkAuthenticationRequired:511};Object.entries(ze).forEach(([e,t])=>{ze[t]=e});const ba=ze;function Wn(e){const t=new ne(e),n=Dn(ne.prototype.request,t);return u.extend(n,ne.prototype,t,{allOwnKeys:!0}),u.extend(n,t,null,{allOwnKeys:!0}),n.create=function(o){return Wn(V(e,o))},n}const w=Wn(ut);w.Axios=ne;w.CanceledError=X;w.CancelToken=pa;w.isCancel=Un;w.VERSION=qn;w.toFormData=he;w.AxiosError=m;w.Cancel=w.CanceledError;w.all=function(t){return Promise.all(t)};w.spread=ma;w.isAxiosError=ga;w.mergeConfig=V;w.AxiosHeaders=C;w.formToJSON=e=>Hn(u.isHTMLForm(e)?new FormData(e):e);w.HttpStatusCode=ba;w.default=w;const ya=w;const ie=new Audio,zn=document.querySelector("body"),U=document.createElement("div"),Ht=document.createElement("h1"),Ut=document.createElement("h3"),Be=document.createElement("i");document.getElementById("app")||(U.id="app",Ht.id="titulo",Be.id="icon",Ut.id="body",U.classList="notificacion hidden",Be.classList="material-icons icon",U.appendChild(Ht),U.appendChild(Be),U.appendChild(Ut),zn.appendChild(U));const wa={apiKey:"AIzaSyBIfYOvo_bj8d-S-X1QOn2gcWGOydXbsEo",authDomain:"chg-intranet.firebaseapp.com",projectId:"chg-intranet",storageBucket:"chg-intranet.appspot.com",messagingSenderId:"897605833714",appId:"1:897605833714:web:eb59785b451f56047e516b",measurementId:"G-057HJWFR36"},Ea=Yt(wa),Kn=Zs(Ea);ei(Kn,{vapidKey:"BPD_s2hjRHnv_mrA1gKto_ISxOpz5a61pUrGfRDYqi_2yFJVXmoHzMrmQJsZYN41yzB0K79chpqwnJqOotJSGFI"}).then(e=>{if(e){const t=document.createElement("input");t.type="hidden",t.value=e,t.id="fireToken",zn.appendChild(t);const n=document.getElementById("_token");ya.post("https://intranet.prigo.com.mx/delivery/saveToken",{token:e,_token:n}).then(function(r){console.log(r)}).catch(function(r){console.log(r)})}else console.log("No registration token available. Request permission to generate one.")}).catch(e=>{console.log("An error occurred while retrieving token. ",e)});ti(Kn,e=>{console.log("Message received. ",e);const t=document.getElementById("app"),n=document.getElementById("titulo"),r=document.getElementById("body"),o=document.getElementById("icon"),s=e.data;t.classList=`notificacion ${s.type}`,n.innerHTML=s.title,r.innerHTML=s.body,o.innerHTML=s.type!="Error"?"check_circle":"cancel",ie.src="/Laravel/resources/views/delivery/delivery.mp3",ie.play()});document.getElementById("app").addEventListener("click",()=>{document.getElementById("app").classList="notificacion hidden",ie.pause(),ie.currentTime=0});
