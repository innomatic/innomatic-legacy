README
======

General information
-------------------

Innomatic is an open source platform for building multi-tenant cloud
applications developed by Innoteam in PHP language.

It is suitable as an Internet/Intranet development and deployment
system, featuring a powerful modular architecture and allowing very fast
deployment and distribution of web based solutions.

Innomatic is not a framework like Symfony or similar.

It is a platform aimed at building, deploying, distributing and managing
multi-tenant applications and quickly enabling them to multiple customers
hosted in a single Innomatic installation.

It also contains a number of mini frameworks, like an MVC system to be used
when building back office oriented applications. Over Innomatic you can also
stack up other layered frameworks (e.g. a Content Management Framework and an
e-Commerce platform using the CMF) and user-centric applications.



Features
--------

- Web interface. The first standard Innomatic interface is the web
administration system.

- Web services interface. The container provides a powerful interface for
XmlRpc web services calls.

- Centralized customers/domains administration. Creation, editing and remotion
of the customer profiles/sites can be done through a single interface.

- Centralized applications administration. Installation, update and
remotion of the applications is done through a single interface.

- Extensibility of container functions. Container functions can be extended
through external applications and hooks.

- Extreme modularity. The whole container is designed with modularity in
mind.

- Interaction between the applications. Every application can interact
with the other applications, through API calls, hooks, web services and other.
The container also provides dependencies support between
applications.

- Immediate deployment and update of applications. To install an
application in the container you only need to upload the application
file through a applications administration page. The same applies for the
update of already installed applications, automatically updated for all the
container sites.

- Deploy applications once - use many times. When installing an
application, it can be enabled to all container sites without
reinstalling it.

- Easy installation and immediate update of the container. Since Innomatic
is seen by itself as a deployable application, it has all of applications
properties and can be updated like any other application with a single step.

- Separation of code and presentation. Application interface is
programmed with a dedicated library of functions; no HTML in code.

- Override system. Applications can be customized without affecting other
domains and without changing the original application code with the override
feature.

- Use of open standards and technologies. Innomatic follows open standards
like SQL, XML, XML-RPC and so on.

- Open source license. Innomatic is licensed with the new BSD License.

- Localization support. Innomatic supports country and language
localization, both at container and applications level.

- Context sensitive help. The system provides online help.

- Database abstraction. The container provides an extensible database
abstraction layer. Current available interfaces: PostgreSQL and MySQL.

- Written in PHP language. Innomatic is written in PHP 5, a language born
and specifically designed for the web.

- Cross platform. Being written in PHP language, Innomatic can be
installed in every operating system where PHP has been ported.



License
-------

Innomatic is released under the new BSD license. See the file named LICENSE.



Requirements
------------

Serverside:
    A web server with PHP >= 5.4 support and a SQL server supported by Innomatic.
    Read INSTALL file for more information.

Clientside:
    A modern web browser.

See the file named INSTALL for more details.



Installation
------------

See the file named INSTALL.



Additional Information
----------------------

Innomatic Platform official web site:
     http://www.innomatic.io/

Innomatic technical wiki:
     http://wiki.innomaticplatform.com/

To submit a bug report:
    https://github.com/innomatic/innomatic/issues

Would you like to participate in developing Innomatic? Send an e-mail at
info@innomatic.io



Disclaimer
----------

There is no warranty, expressed or implied, associated with this product.
Use at your own risk.
