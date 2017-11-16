INSERT INTO llx_c_tvi_carrosserie (rowid, carrosserie) VALUES
(1, 'Fourgon'),
(2, 'Tautliner'),
(3, 'Ampiroll'),
(4, 'Frigo'),
(5, 'Caisse Mobile'),
(6, 'Plateau'),
(7, 'Plateau grue'),
(8, 'Benne TP'),
(9, 'Benne TP + Grue');

INSERT INTO llx_c_tvi_genre (rowid, genre) VALUES
(1, 'Porteur'),
(2, 'Tracteur Routier'),
(3, 'Véhicule Utilitaire léger'),
(4, 'Véhicule léger'),
(5, 'Remorque'),
(6, 'Semie Remorque'),
(7, 'Ensemble articulé');

INSERT INTO llx_c_tvi_sites (rowid, codesite, nom) VALUES
(1, 'ENN', 'Ennery'),
(2, 'LUD', 'Ludres'),
(3, 'NAB', 'Saint Nabors'),
(4, 'SAR', 'Sarreguemines'),
(5, 'YTZ', 'Yutz');

INSERT INTO llx_c_tvi_marques (rowid, marque) VALUES
(1, 'Volvo'),
(2, 'Renault'),
(3, 'Mercedes'),
(4, 'MAN'),
(5, 'DAF'),
(6, 'Iveco'),
(7, 'Nissan'),
(8, 'Scania');

INSERT INTO llx_c_tvi_normes (rowid, norme) VALUES
(1, 'EUR1'),
(2, 'EUR2'),
(3, 'EUR3'),
(4, 'EUR4'),
(5, 'EUR5'),
(6, 'EUR6'),
(7, 'EEV');

INSERT INTO llx_c_tvi_ralentisseur (rowid, ralentisseur) VALUES
(1, 'V.E.B.'),
(2, 'V.E.B. +'),
(3, 'Hydraulique'),
(4, 'Electrique'),
(5, 'Sans Ralentisseur');

INSERT INTO llx_c_tvi_silouhette (rowid, silouhette) VALUES
(1, '4x2'),
(2, '4x4'),
(3, '6x2'),
(4, '6x4'),
(5, '6x6'),
(6, '8x2'),
(7, '8x4'),
(8, '8x6'),
(9, '8x8'),
(10, '1 Essieu'),
(11, '2 Essieux'),
(12, '3 Essieux');


INSERT INTO llx_c_tvi_solutions_transport (rowid, nom, active) VALUES
(1, 'FIN: Financement VFS', 1),
(2, 'FIN: Financement Lixbail', 1),
(3, 'DFOL: Dynafleet Fuel et Environnement', 1),
(4, 'DFOL: Dynafleet Positionnement', 1),
(5, 'DFOL: Dynafleet Positionnement +', 1),
(6, 'DFOL: Dynafleet Driver Time Management', 1),
(7, 'DFOL: Dynafleet Messagerie', 1),
(8, 'VCM: Pack Prévention', 1),
(9, 'VCM: Pack Protection cinématique', 1),
(10, 'VCM: Pack Protection véhicule', 1),
(11, 'VCM: Pack Blue', 1),
(12, 'VCM: Pack Silver', 1),
(13, 'VCM: Pack Silver +', 1),
(14, 'VCM: Contrat GOLD', 1),
(15, 'Fuel Advice', 1),
(16, 'Driver Dev', 1);


DELETE FROM tvi_cronjob WHERE methodename='createInvoiceFromContract' AND objectname='Tvi';
INSERT INTO tvi_cronjob (rowid,tms,datec,jobtype,label,command,classesname,objectname,methodename,params,md5params,module_name,priority,datelastrun,datenextrun,datestart,dateend,datelastresult,lastresult,lastoutput,unitfrequency,frequency,maxrun,nbrun,autodelete,status,test,fk_user_author,fk_user_mod,fk_mailing,note,libname,entity) VALUES (103571,'2016-11-17 08:12:51','2016-11-16 18:08:41','method','Facture de contrat','','tvi/class/tvi.class.php','Tvi','createInvoiceFromContract','','','tvi',0,'2016-11-17 08:12:51',NULL,'2016-11-15 00:00:00',NULL,NULL,'1','','604800',1,0,0,0,1,NULL,1,1,NULL,'',NULL,0);

DELETE FROM tvi_c_actioncomm WHERE code='AC_MIN';
INSERT INTO tvi_c_actioncomm (id, code, type, libelle, module, active, todo, color, position) VALUES
(51, 'AC_MIN', 'user', 'Passage aux Mines', NULL, 1, NULL, NULL, 12);
DELETE FROM tvi_c_actioncomm WHERE code='AC_TACHY';
INSERT INTO tvi_c_actioncomm (id, code, type, libelle, module, active, todo, color, position) VALUES
(52, 'AC_TACHY', 'user', 'Controle Tachygraphe', NULL, 1, NULL, NULL, 13);
DELETE FROM tvi_c_actioncomm WHERE code='AC_LIM';
INSERT INTO tvi_c_actioncomm (id, code, type, libelle, module, active, todo, color, position) VALUES
(53, 'AC_LIM', 'user', 'Controle Limiteur', NULL, 1, NULL, NULL, 14);
DELETE FROM tvi_c_actioncomm WHERE code='AC_EXT';
INSERT INTO tvi_c_actioncomm (id, code, type, libelle, module, active, todo, color, position) VALUES
(54, 'AC_EXT', 'user', 'Controle Extincteur', NULL, 1, NULL, NULL, 15);
DELETE FROM tvi_c_actioncomm WHERE code='AC_HAY';
INSERT INTO tvi_c_actioncomm (id, code, type, libelle, module, active, todo, color, position) VALUES
(55, 'AC_HAY', 'user', 'Controle Hayon', NULL, 1, NULL, NULL, 16);

TRUNCATE TABLE tvi_event_period;
INSERT INTO tvi_event_period (rowid, fk_genre, fk_typeevent, franchise, periode) VALUES
(1, 2, 51, 12, 12),
(2, 2, 53, 24, 24),
(3, 2, 52, 24, 24),
(4, 2, 54, 12, 12),
(5, 1, 51, 12, 12),
(6, 1, 52, 24, 24),
(7, 1, 53, 24, 24),
(8, 1, 55, 6, 6),
(9, 1, 54, 12, 12),
(10, 3, 51, 60, 12),
(11, 3, 54, 12, 12),
(12, 3, 55, 6, 6),
(13, 4, 51, 60, 12),
(14, 5, 51, 12, 12),
(15, 5, 54, 12, 12),
(16, 5, 55, 6, 6),
(17, 6, 51, 12, 12),
(18, 6, 54, 12, 12),
(19, 6, 55, 6, 6),
(20, 7, 51, 12, 12),
(21, 7, 52, 24, 24),
(22, 7, 53, 24, 24),
(23, 7, 54, 12, 12),
(24, 7, 55, 6, 6);


INSERT INTO tvi_document_model (nom,entity,type,libelle,description) VALUES ('contrattvi',1,'contract','contrattvi',NULL);
