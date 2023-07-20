# XQL

<strong>This is now a public archive and has been redesigned as WindsorDB</strong>

The purpose of this library is to map database tables and fields, their relations, and the analytic / use-case specific results to a single file stored in a cloud storage product like Amazon S3.

<strong>In development (not stable at all, although the current priority)</strong><br><br>

When working with a lot of data that has multiple relationships including multiple parent-child relationships, XQL will automate the data workflow and create an easy interface for the data retrieval. By making use of the XQLModel class, you can define a schema, bind values from a database or other model to update all usages automatically, hook onto events like 'on create', and process the data in pre-defined functions. The collection of objects can then be stored for immediate retrieval with the latest data. This codebase can eventually be adapted for use in an API or single database interface. Currently, the focus is on cloud storage and XML due to the need to offload the database workload and number of entries.
