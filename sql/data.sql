
DELETE FROM llx_cronjob WHERE methodename='createInvoiceFromContract' AND objectname='Tvi';
INSERT INTO llx_cronjob (rowid,tms,datec,jobtype,label,command,classesname,objectname,methodename,params,md5params,module_name,priority,datelastrun,datenextrun,datestart,dateend,datelastresult,lastresult,lastoutput,unitfrequency,frequency,maxrun,nbrun,autodelete,status,test,fk_user_author,fk_user_mod,fk_mailing,note,libname,entity) VALUES (103571,'2016-11-17 08:12:51','2016-11-16 18:08:41','method','Facture de contrat','','tvi/class/tvi.class.php','Tvi','createInvoiceFromContract','','','tvi',0,'2016-11-17 08:12:51',NULL,'2016-11-15 00:00:00',NULL,NULL,'1','','604800',1,0,0,0,1,NULL,1,1,NULL,'',NULL,0);

INSERT INTO llx_document_model (nom,entity,type,libelle,description) VALUES ('contrattvi',1,'contract','contrattvi',NULL);
