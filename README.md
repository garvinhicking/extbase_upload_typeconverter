You had these options:

* Completely manually handle $_FILES / PSR-7
* Helhum upload_example TypeConverter 

For that you need:

* A TypeConverter
* Registration of TypeConverter (Services)
* Import of TypeConverter in Controller
* Initialization of TypeConverter options in Controller Action
* Custom Fluid ViewHelper as "custom:upload" instead of "f:form.upload"
* Import and usage of custom Fluid ViewHelper
* A Custom "FileReference" Domain Model, Repository and Extbase mapping to sys_file_reference.
