## Magnus.Router Object Router

Object Router implementations for MagnusCore based on Dispatch protocol

    © 2017 MagnusCore and contributors.

    https://github.com/MagnusCore/Magnus.Router



Introduction
------------

Object router is simply a flavor of the routing process that attempts to resolve path elements as a chain of object attributes. This is contrary to the typical routing process involving the use of regex matching in PHP web frameworks. The main cost of this regex matching is the O(n) worst-case performance, in some cases the router continues to seek for more specific routes resulting in every single route being evaluated at least once. This can get particularly nasty in the case of issuing a 404. Certain router implementations will attempt to coerce this process in something resembling a tree for performance gains at great cost of readability. With Object routing, the best AND worst case scenario is O(depth). If a HTTP: Not Found is raised, this router can terminate immediately.