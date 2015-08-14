Chat installation for e-marking
-------------------------------

In order to install the chat feature in e-marking a Node.js server is
required

The Node.js version for all tests is 0.12.4

INSTALLATION
------------

For installing Node.js please refer to its website's instructions in
https://nodejs.org/download/

We usually install it in C:\nodejs for Windows environments and
/opt/nodejs for Linux or Mac environments

Once you succesfully install Node.js, please verify its version using:
node -v
It should show:
v0.12.4

For the rest the steps are:

- Create a moodle directory in your nodejs installation (for this example
we will assume a Windows installation, therefore the new directory will be
C:\nodejs\moodle).

- Copy the mod/emarking/marking/chat/server.js to your Moodle directory in nodejs

- Use the command line interface to install the nodejs dependencies using npm

npm install moment
npm install socket.io
npm install underscore

Most installations should run without warnings except for socket.io which
will print:
------------------------
C:\nodejs\moodle>npm install socket.io
|


> ws@0.5.0 install C:\nodejs\node_modules\socket.io\node_modules\engine.io\node_
modules\ws
> (node-gyp rebuild 2> builderror.log) || (exit 0)


C:\nodejs\node_modules\socket.io\node_modules\engine.io\node_modules\ws>if not d
efined npm_config_node_gyp (node "C:\nodejs\node_modules\npm\bin\node-gyp-bin\\.
.\..\node_modules\node-gyp\bin\node-gyp.js" rebuild )  else (rebuild)
|


> ws@0.4.31 install C:\nodejs\node_modules\socket.io\node_modules\socket.io-clie
nt\node_modules\engine.io-client\node_modules\ws
> (node-gyp rebuild 2> builderror.log) || (exit 0)

/
C:\nodejs\node_modules\socket.io\node_modules\socket.io-client\node_modules\engi
ne.io-client\node_modules\ws>if not defined npm_config_node_gyp (node "C:\nodejs
\node_modules\npm\bin\node-gyp-bin\\..\..\node_modules\node-gyp\bin\node-gyp.js"
 rebuild )  else (rebuild)
socket.io@1.3.5 ..\node_modules\socket.io
├── has-binary-data@0.1.3 (isarray@0.0.1)
├── debug@2.1.0 (ms@0.6.2)
├── socket.io-parser@2.2.4 (isarray@0.0.1, debug@0.7.4, component-emitter@1.1.2,
 benchmark@1.0.0, json3@3.2.6)
├── socket.io-adapter@0.3.1 (object-keys@1.0.1, debug@1.0.2, socket.io-parser@2.
2.2)
├── engine.io@1.5.1 (base64id@0.1.0, debug@1.0.3, engine.io-parser@1.2.1, ws@0.5
.0)
└── socket.io-client@1.3.5 (to-array@0.1.3, indexof@0.0.1, component-bind@1.0.0,
 debug@0.7.4, backo2@1.0.2, object-component@0.0.3, component-emitter@1.1.2, has
-binary@0.1.6, parseuri@0.0.2, engine.io-client@1.5.1)
------------------------

- Run node with:
node server.js

It should show:
[08-07-2015 16:25:06] Iniciando Servidor
[08-07-2015 16:25:06] Servidor iniciado exitosamente en 127.0.0.1:9091
