## Magnus.Router

Router implementations for MagnusCore based on Dispatch protocol

    © 2017 MagnusCore and contributors.

    https://github.com/MagnusCore/Magnus.Router



Introduction
------------

Routing is the process of taking some starting point and a path, then resolving the object that path refers to as a handler. This process is common to almost every web application framework (transforming URLs into controllers), RPC system, and even filesystem shell. Other terms for this process include: "traversal", "dispatch", or "lookup".

This router is based on a [dispatch protocol](https://github.com/marrow/Webcore/wiki/Dispatch-Protocol) and is not intended for direct use but rather as part of a framework. This does not mean that router cannot be used by itself.
