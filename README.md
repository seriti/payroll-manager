# Payroll manager module. 

## Designed for managing staff payroll for a small business.

The following functionality is curremntly supported. More will be added over time.

- Setup unlimited companies
- Manage documents and notes for each company
- Add unlimited staff members to each company
- Manage documents and notes for reach staff member
- Manage departments, positions, salary scales for each company
- Manage monthly wage daily data for each staff member including: days worked, days sick, days absent, days xtra...etc
- Manage simple loans, advances, pension values for each staff member
- Setup your own tax and pension calculations using Formula class.  
- Import monthly staff basic data from Excel/Csv and automatically calculate pension/loan/tax amounts on a rolling basis.
- Monthly Payslip reports

## Requires Seriti Slim 3 MySQL Framework skeleton

This module integrates seamlessly into [Seriti skeleton framework](https://github.com/seriti/slim3-skeleton).  
You need to first install the skeleton framework and then download the source files for the module and follow these instructions.

It is possible to use this module independantly from the seriti skeleton but you will still need the [Seriti tools library](https://github.com/seriti/tools).  
It is strongly recommended that you first install the seriti skeleton to see a working example of code use before using it within another application framework.  
That said, if you are an experienced PHP programmer you will have no problem doing this and the required code footprint is very small.  

## Install the module

1.) Install Seriti Skeleton framework(see the framework readme for detailed instructions):   
    **composer create-project seriti/slim3-skeleton [directory-for-app]**.   
    Make sure that you have thsi working before you proceed.

2.) Download a copy of payroll-manager module source code directly from github and unzip,  
or by using **git clone https://github.com/seriti/payroll-manager** from command line.  
Once you have a local copy of module code check that it has following structure:

/Payroll/(all module implementation classes are in this folder)  
/setup_app.php  
/routes.php  
/templates/payroll/(all module templates)

3.) Copy the **payroll** folder and all its contents into **[directory-for-app]/app** folder.

4.) Open the routes.php file and insert the **$this->group('/payroll', function (){}** route definition block
within the existing  **$app->group('/admin', function () {}** code block contained in existing skeleton **[directory-for-app]/src/routes.php** file.

5.) Open the setup_app.php file and  add the module config code snippet into bottom of skeleton **[directory-for-app]/src/setup_app.php** file.  
Please check the **table_prefix** value to ensure that there will not be a clash with any existing tables in your database.

6.) Copy the contents of "templates" folder to **[directory-for-app]/templates/** folder
 
7.) Now in your browser goto URL:  

"http://localhost:8000/admin/payroll/dashboard" if you are using php built in server  
OR  
"http://www.yourdomain.com/admin/payroll/dashboard" if you have configured a domain on your server  
OR
Click **Dashboard** menu option and you will see list of available modules, click **Payroll manager**  

Now click link at bottom of page **Setup Database**: This will create all necessary database tables with table_prefix as defined above.  
Thats it, you are good to go. Click on "Setup" sub-menu tab and Add a company, then add departents/positions/scales for that company, then click "Staff" to start adding staff mebers to the current active company.
