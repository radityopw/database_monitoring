----------------------------------------------------------------
-- AdventureWorks2016_EXT samples: Row-Level Security
----------------------------------------------------------------

-- This demo uses the Sales.CustomerPII table in the AdventureWorks2016_EXT sample database
-- to demonstrate how Row-Level Security (RLS) can be used to limit access to rows based on 
-- the identity, roles, or execution context of the user executing a query.
--
-- First, we'll show the effects of RLS by impersonating various users and seeing what they
-- can access. Then we'll show how RLS was set up to enable this behavior.
USE AdventureWorks2016_EXT
go

----------------------------------------------------------------
-- PART 1: What RLS looks like
----------------------------------------------------------------
-- RLS is already enabled in the sample database to limit access to sensitive data about 
-- AdventureWorks customers, contained in the Sales.CustomerPII table. The access logic is:
--   - Sales Persons should only be able to view customers who are in their assigned territory.
--   - Managers and VPs in the Sales org should be able to see all customers.

-- EXAMPLE 1:
-- The user 'michael9' is a member of the SalesPersons role, so he can only access customers 
-- who are in his assigned territory (Territory 2):
EXECUTE AS USER = 'michael9'
go

-- Only customers for Territory 2 are visible (other territories are filtered)
SELECT * FROM Sales.CustomerPII 
go

-- Blocked from inserting a new customer in a territory not assigned to him...
INSERT INTO Sales.CustomerPII (CustomerID, FirstName, LastName, TerritoryID)
VALUES (0, 'Bad', 'Customer', 10) -- operation failed, block predicate conflicts

-- ...but can insert a new customer in a territory assigned to him
INSERT INTO Sales.CustomerPII (CustomerID, FirstName, LastName, TerritoryID)
VALUES (0, 'Good', 'Customer', 2) -- 1 row affected


REVERT
go


-- EXAMPLE 2:
-- On the other hand, the user 'amy0' is a member of the SalesManagers role, so she can access
-- all customers in all territories:
EXECUTE AS USER = 'amy0' -- Manager
go

SELECT * FROM Sales.CustomerPII -- all customers are visible
go

-- ...but can insert a new customer in a territory assigned to him
INSERT INTO Sales.CustomerPII (CustomerID, FirstName, LastName, TerritoryID)
VALUES (0, 'Good', 'Customer', 2) -- 1 row affected

REVERT
go

----------------------------------------------------------------
-- PART 2: How to configure RLS
----------------------------------------------------------------
-- Let's change the security policy to support a common scenario for web applications. In this scenario,
-- Sales Persons connect to the database through a middle-tier application using a shared SQL login. To 
-- identify the current application user in the database, the application will store the current user name 
-- in the SESSION_CONTEXT immediately after opening a connection. This way, the RLS policy can filter rows 
-- based on the user name stored in SESSION_CONTEXT.

-- First, create a shared SQL login for the application's connection string
CREATE LOGIN ApplicationServiceAccount WITH PASSWORD = '{SomeStrongPassword}'
CREATE USER ApplicationServiceAccount FOR LOGIN ApplicationServiceAccount
GRANT SELECT, INSERT, UPDATE, DELETE ON Sales.CustomerPII TO ApplicationServiceAccount
go

-- To set the SESSION_CONTEXT, the application will execute the following each time a connection is opened:
EXEC sp_set_session_context @key=N'user_name', @value=N'michael9' -- for example, the Sales Person from above
go

-- Now, this user name is stored in the SESSION_CONTEXT for the rest of the session (it will be reset when the
-- connection is closed and returned to the connection pool).
SELECT SESSION_CONTEXT(N'user_name')
go

-- Reset for now
EXEC sp_set_session_context @key=N'user_name', @value=NULL
go

-- We need to change our security policy to filter based on the user_name stored in SESSION_CONTEXT. To do this,
-- create a new predicate function that adds the new access logic. As a best practice, we'll put the function in a 
-- separate 'Security' schema that we've already created.
CREATE FUNCTION Security.customerAccessPredicate_v2(@TerritoryID int)
	RETURNS TABLE
	WITH SCHEMABINDING
AS
	RETURN SELECT 1 AS accessResult
	FROM HumanResources.Employee e 
	INNER JOIN Sales.SalesPerson sp ON sp.BusinessEntityID = e.BusinessEntityID
	WHERE
		-- SalesPersons can only access customers in assigned territory
		( IS_MEMBER('SalesPersons') = 1
			AND RIGHT(e.LoginID, LEN(e.LoginID) - LEN('adventure-works\')) = USER_NAME() 
			AND sp.TerritoryID = @TerritoryID ) 
		
		-- SalesManagers and database administrators can access all customers
		OR IS_MEMBER('SalesManagers') = 1
		OR IS_MEMBER('db_owner') = 1

		-- NEW: Use the user_name stored in SESSION_CONTEXT if ApplicationServiceAccount is connected
		OR ( USER_NAME() = 'ApplicationServiceAccount' 
			AND RIGHT(e.LoginID, LEN(e.LoginID) - LEN('adventure-works\')) = CAST(SESSION_CONTEXT(N'user_name') AS sysname)
			AND sp.TerritoryID = @TerritoryID )
go

-- Swap this new function into the existing security policy. The FILTER predicate filters which rows
-- are accessible via SELECT, UPDATE, and DELETE. The BLOCK predicate will prevent users from INSERT-ing or
-- UPDATE-ing rows such that they violate the predicate.
ALTER SECURITY POLICY Security.customerPolicy
	ALTER FILTER PREDICATE Security.customerAccessPredicate_v2(TerritoryID) ON Sales.CustomerPII,
	ALTER BLOCK PREDICATE Security.customerAccessPredicate_v2(TerritoryID) ON Sales.CustomerPII
go

-- To simulate the application, impersonate ApplicationServiceAccount
EXECUTE AS USER = 'ApplicationServiceAccount'
go

-- If the application has not set the user_name key in SESSION_CONTEXT (i.e. it's NULL), then all rows are filtered:
SELECT * FROM Sales.CustomerPII -- 0 rows
go

-- So the application should set the current user_name in SESSION_CONTEXT immediately after opening a connection:
EXEC sp_set_session_context @key=N'user_name', @value=N'michael9' -- assume 'michael9' is logged in to the application
go

-- Only customers for Territory 2 are visible
SELECT * FROM Sales.CustomerPII
go

-- Application is blocked from inserting a new customer in a territory not assigned to the current user...
INSERT INTO Sales.CustomerPII (CustomerID, FirstName, LastName, TerritoryID)
VALUES (0, 'Bad', 'Customer', 10) -- operation failed, block predicate conflicts
go

REVERT
go

-- Reset the changes
EXEC sp_set_session_context @key=N'user_name', @value=NULL
go

ALTER SECURITY POLICY Security.customerPolicy
	ALTER FILTER PREDICATE Security.customerAccessPredicate(TerritoryID) ON Sales.CustomerPII,
	ALTER BLOCK PREDICATE Security.customerAccessPredicate(TerritoryID) ON Sales.CustomerPII
go

DROP FUNCTION Security.customerAccessPredicate_v2
DROP USER ApplicationServiceAccount
DROP LOGIN ApplicationServiceAccount
go


-- Final note: Use these system views to monitor and manage security policies and predicates
SELECT * FROM sys.security_policies
SELECT * FROM sys.security_predicates
go
----------------------------------------------------------------
-- AdventureWorks2016_EXT samples: Row-Level Security
----------------------------------------------------------------

-- This demo uses the Sales.CustomerPII table in the AdventureWorks2016_EXT sample database
-- to demonstrate how Row-Level Security (RLS) can be used to limit access to rows based on 
-- the identity, roles, or execution context of the user executing a query.
--
-- First, we'll show the effects of RLS by impersonating various users and seeing what they
-- can access. Then we'll show how RLS was set up to enable this behavior.
USE AdventureWorks2016_EXT
go

----------------------------------------------------------------
-- PART 1: What RLS looks like
----------------------------------------------------------------
-- RLS is already enabled in the sample database to limit access to sensitive data about 
-- AdventureWorks customers, contained in the Sales.CustomerPII table. The access logic is:
--   - Sales Persons should only be able to view customers who are in their assigned territory.
--   - Managers and VPs in the Sales org should be able to see all customers.

-- EXAMPLE 1:
-- The user 'michael9' is a member of the SalesPersons role, so he can only access customers 
-- who are in his assigned territory (Territory 2):
EXECUTE AS USER = 'michael9'
go

-- Only customers for Territory 2 are visible (other territories are filtered)
SELECT * FROM Sales.CustomerPII 
go

-- Cannot update or delete customers who are not in Territory 2 (other territories are filtered)
DELETE FROM Sales.CustomerPII WHERE TerritoryID = 10 -- 0 rows affected
UPDATE Sales.CustomerPII SET FirstName = 'Changed' WHERE TerritoryID = 9 -- 0 rows affected
go

-- Blocked from inserting a new customer in a territory not assigned to him...
INSERT INTO Sales.CustomerPII (CustomerID, FirstName, LastName, TerritoryID)
VALUES (0, 'Bad', 'Customer', 10) -- operation failed, block predicate conflicts

-- ...but can insert a new customer in a territory assigned to him
INSERT INTO Sales.CustomerPII (CustomerID, FirstName, LastName, TerritoryID)
VALUES (0, 'Good', 'Customer', 2) -- 1 row affected

-- Blocked from updating the territory of an accessible customer to be in an unassigned territory
UPDATE Sales.CustomerPII SET TerritoryID = 7 WHERE CustomerID = 0 -- operation failed, block predicate conflicts

-- Reset the changes
DELETE FROM Sales.CustomerPII WHERE CustomerID = 0
go

REVERT
go


-- EXAMPLE 2:
-- On the other hand, the user 'amy0' is a member of the SalesManagers role, so she can access
-- all customers in all territories:
EXECUTE AS USER = 'amy0' -- Manager
go

SELECT * FROM Sales.CustomerPII -- all customers are visible
go

REVERT
go

----------------------------------------------------------------
-- PART 2: How to configure RLS
----------------------------------------------------------------
-- Let's change the security policy to support a common scenario for web applications. In this scenario,
-- Sales Persons connect to the database through a middle-tier application using a shared SQL login. To 
-- identify the current application user in the database, the application will store the current user name 
-- in the SESSION_CONTEXT immediately after opening a connection. This way, the RLS policy can filter rows 
-- based on the user name stored in SESSION_CONTEXT.

-- First, create a shared SQL login for the application's connection string
CREATE LOGIN ApplicationServiceAccount WITH PASSWORD = '{SomeStrongPassword}'
CREATE USER ApplicationServiceAccount FOR LOGIN ApplicationServiceAccount
GRANT SELECT, INSERT, UPDATE, DELETE ON Sales.CustomerPII TO ApplicationServiceAccount
go

-- To set the SESSION_CONTEXT, the application will execute the following each time a connection is opened:
EXEC sp_set_session_context @key=N'user_name', @value=N'michael9' -- for example, the Sales Person from above
go

-- Now, this user name is stored in the SESSION_CONTEXT for the rest of the session (it will be reset when the
-- connection is closed and returned to the connection pool).
SELECT SESSION_CONTEXT(N'user_name')
go

-- Reset for now
EXEC sp_set_session_context @key=N'user_name', @value=NULL
go

-- We need to change our security policy to filter based on the user_name stored in SESSION_CONTEXT. To do this,
-- create a new predicate function that adds the new access logic. As a best practice, we'll put the function in a 
-- separate 'Security' schema that we've already created.
CREATE FUNCTION Security.customerAccessPredicate_v2(@TerritoryID int)
	RETURNS TABLE
	WITH SCHEMABINDING
AS
	RETURN SELECT 1 AS accessResult
	FROM HumanResources.Employee e 
	INNER JOIN Sales.SalesPerson sp ON sp.BusinessEntityID = e.BusinessEntityID
	WHERE
		-- SalesPersons can only access customers in assigned territory
		( IS_MEMBER('SalesPersons') = 1
			AND RIGHT(e.LoginID, LEN(e.LoginID) - LEN('adventure-works\')) = USER_NAME() 
			AND sp.TerritoryID = @TerritoryID ) 
		
		-- SalesManagers and database administrators can access all customers
		OR IS_MEMBER('SalesManagers') = 1
		OR IS_MEMBER('db_owner') = 1

		-- NEW: Use the user_name stored in SESSION_CONTEXT if ApplicationServiceAccount is connected
		OR ( USER_NAME() = 'ApplicationServiceAccount' 
			AND RIGHT(e.LoginID, LEN(e.LoginID) - LEN('adventure-works\')) = CAST(SESSION_CONTEXT(N'user_name') AS sysname)
			AND sp.TerritoryID = @TerritoryID )
go

-- Swap this new function into the existing security policy. The FILTER predicate filters which rows
-- are accessible via SELECT, UPDATE, and DELETE. The BLOCK predicate will prevent users from INSERT-ing or
-- UPDATE-ing rows such that they violate the predicate.
ALTER SECURITY POLICY Security.customerPolicy
	ALTER FILTER PREDICATE Security.customerAccessPredicate_v2(TerritoryID) ON Sales.CustomerPII,
	ALTER BLOCK PREDICATE Security.customerAccessPredicate_v2(TerritoryID) ON Sales.CustomerPII
go

-- To simulate the application, impersonate ApplicationServiceAccount
EXECUTE AS USER = 'ApplicationServiceAccount'
go

-- If the application has not set the user_name key in SESSION_CONTEXT (i.e. it's NULL), then all rows are filtered:
SELECT * FROM Sales.CustomerPII -- 0 rows
go

-- So the application should set the current user_name in SESSION_CONTEXT immediately after opening a connection:
EXEC sp_set_session_context @key=N'user_name', @value=N'michael9' -- assume 'michael9' is logged in to the application
go

-- Only customers for Territory 2 are visible
SELECT * FROM Sales.CustomerPII
go

-- Application is blocked from inserting a new customer in a territory not assigned to the current user...
INSERT INTO Sales.CustomerPII (CustomerID, FirstName, LastName, TerritoryID)
VALUES (0, 'Bad', 'Customer', 10) -- operation failed, block predicate conflicts
go

REVERT
go

-- Reset the changes
EXEC sp_set_session_context @key=N'user_name', @value=NULL
go

ALTER SECURITY POLICY Security.customerPolicy
	ALTER FILTER PREDICATE Security.customerAccessPredicate(TerritoryID) ON Sales.CustomerPII,
	ALTER BLOCK PREDICATE Security.customerAccessPredicate(TerritoryID) ON Sales.CustomerPII
go

DROP FUNCTION Security.customerAccessPredicate_v2
DROP USER ApplicationServiceAccount
DROP LOGIN ApplicationServiceAccount
go


-- Final note: Use these system views to monitor and manage security policies and predicates
SELECT * FROM sys.security_policies
SELECT * FROM sys.security_predicates
go
