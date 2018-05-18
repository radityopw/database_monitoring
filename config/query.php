<?php

return [
    'sqlserver' => [
        'extract' => "
            DECLARE @Command nvarchar(MAX)
            SET @Command = '
            SELECT
                [UserType] = CASE princ.[type]
                                WHEN ''S'' THEN ''SQL User''
                                WHEN ''U'' THEN ''Windows User''
                                WHEN ''G'' THEN ''Windows Group''
                    WHEN ''A'' THEN ''Application User''
                            END,
                [DatabaseUserName] = princ.[name],
                [LoginName]        = ulogin.[name],
                [Role]             = NULL,
                [PermissionType]   = perm.[permission_name],
                [PermissionState]  = perm.[state_desc],
                [ObjectType] = CASE perm.[class]
                                WHEN 1 THEN obj.[type_desc]        
                                ELSE perm.[class_desc]             
                            END,
                [Schema] = objschem.[name],
                [ObjectName] = CASE perm.[class]
                                WHEN 3 THEN permschem.[name]       
                                WHEN 4 THEN imp.[name]             
                                ELSE OBJECT_NAME(perm.[major_id])  
                            END,
                [ColumnName] = col.[name]
            FROM
                sys.database_principals            AS princ
                LEFT JOIN sys.server_principals    AS ulogin    ON ulogin.[sid] = princ.[sid]
                LEFT JOIN sys.database_permissions AS perm      ON perm.[grantee_principal_id] = princ.[principal_id]
                LEFT JOIN sys.schemas              AS permschem ON permschem.[schema_id] = perm.[major_id]
                LEFT JOIN sys.objects              AS obj       ON obj.[object_id] = perm.[major_id]
                LEFT JOIN sys.schemas              AS objschem  ON objschem.[schema_id] = obj.[schema_id]
                LEFT JOIN sys.columns              AS col       ON col.[object_id] = perm.[major_id]
                                                                AND col.[column_id] = perm.[minor_id]
                LEFT JOIN sys.database_principals  AS imp       ON imp.[principal_id] = perm.[major_id]
            WHERE
                princ.[type] IN (''S'',''U'',''G'',''A'')
                AND princ.[name] NOT IN (''sys'', ''INFORMATION_SCHEMA'')
            UNION
            SELECT
                [UserType] = CASE membprinc.[type]
                                WHEN ''S'' THEN ''SQL User''
                                WHEN ''U'' THEN ''Windows User''
                                WHEN ''G'' THEN ''Windows Group''
                    WHEN ''A'' THEN ''Application User''
                            END,
                [DatabaseUserName] = membprinc.[name],
                [LoginName]        = ulogin.[name],
                [Role]             = roleprinc.[name],
                [PermissionType]   = perm.[permission_name],
                [PermissionState]  = perm.[state_desc],
                [ObjectType] = CASE perm.[class]
                                WHEN 1 THEN obj.[type_desc]        
                                ELSE perm.[class_desc]             
                            END,
                [Schema] = objschem.[name],
                [ObjectName] = CASE perm.[class]
                                WHEN 3 THEN permschem.[name]       
                                WHEN 4 THEN imp.[name]             
                                ELSE OBJECT_NAME(perm.[major_id])  
                            END,
                [ColumnName] = col.[name]
            FROM

                sys.database_role_members          AS members
                
                JOIN      sys.database_principals  AS roleprinc ON roleprinc.[principal_id] = members.[role_principal_id]

                JOIN      sys.database_principals  AS membprinc ON membprinc.[principal_id] = members.[member_principal_id]

                LEFT JOIN sys.server_principals    AS ulogin    ON ulogin.[sid] = membprinc.[sid]

                LEFT JOIN sys.database_permissions AS perm      ON perm.[grantee_principal_id] = roleprinc.[principal_id]
                LEFT JOIN sys.schemas              AS permschem ON permschem.[schema_id] = perm.[major_id]
                LEFT JOIN sys.objects              AS obj       ON obj.[object_id] = perm.[major_id]
                LEFT JOIN sys.schemas              AS objschem  ON objschem.[schema_id] = obj.[schema_id]
                
                LEFT JOIN sys.columns              AS col       ON col.[object_id] = perm.[major_id]
                                                                AND col.[column_id] = perm.[minor_id]
                
                LEFT JOIN sys.database_principals  AS imp       ON imp.[principal_id] = perm.[major_id]
            WHERE
                membprinc.[type] IN (''S'',''U'',''G'',''A'')

                AND membprinc.[name] NOT IN (''sys'', ''INFORMATION_SCHEMA'')

            UNION


            SELECT
                [UserType]         = ''{All Users}'',
                [DatabaseUserName] = ''{All Users}'',
                [LoginName]        = ''{All Users}'',
                [Role]             = roleprinc.[name],
                [PermissionType]   = perm.[permission_name],
                [PermissionState]  = perm.[state_desc],
                [ObjectType] = CASE perm.[class]
                                WHEN 1 THEN obj.[type_desc]        -- Schema-contained objects
                                ELSE perm.[class_desc]             -- Higher-level objects
                            END,
                [Schema] = objschem.[name],
                [ObjectName] = CASE perm.[class]
                                WHEN 3 THEN permschem.[name]       -- Schemas
                                WHEN 4 THEN imp.[name]             -- Impersonations
                                ELSE OBJECT_NAME(perm.[major_id])  -- General objects
                            END,
                [ColumnName] = col.[name]
            FROM

                sys.database_principals            AS roleprinc
                
                LEFT JOIN sys.database_permissions AS perm      ON perm.[grantee_principal_id] = roleprinc.[principal_id]
                LEFT JOIN sys.schemas              AS permschem ON permschem.[schema_id] = perm.[major_id]

                JOIN      sys.objects              AS obj       ON obj.[object_id] = perm.[major_id]
                LEFT JOIN sys.schemas              AS objschem  ON objschem.[schema_id] = obj.[schema_id]
                
                LEFT JOIN sys.columns              AS col       ON col.[object_id] = perm.[major_id]
                                                                AND col.[column_id] = perm.[minor_id]

                LEFT JOIN sys.database_principals  AS imp       ON imp.[principal_id] = perm.[major_id]
            WHERE
                roleprinc.[type] = ''R''
                AND roleprinc.[name] = ''public''
                AND obj.[is_ms_shipped] = 0

            ORDER BY
            [UserType],
            [DatabaseUserName],
            [LoginName],
            [Role],
            [Schema],
            [ObjectName],
            [ColumnName],
            [PermissionType],
            [PermissionState],
            [ObjectType] '

            EXEC sp_executesql @Command
        ",
    ],
];