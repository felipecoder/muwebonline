IF NOT EXISTS (SELECT *
FROM sys.objects
WHERE object_id = OBJECT_ID(N'[dbo].[MWO_hashmd5]') AND type in (N'FN', N'IF', N'TF', N'FS', N'FT'))
BEGIN
	execute dbo.sp_executesql @statement = N'CREATE  FUNCTION [dbo].[MWO_hashmd5] (@data VARCHAR(10), @data2 VARCHAR(10))
  RETURNS BINARY(16) AS
	BEGIN
	DECLARE @hash BINARY(16)
	EXEC master.dbo.XP_MD5_EncodeKeyVal @data2, @data, @hash OUT
	RETURN @hash
	END'
END;

IF OBJECT_ID('dbo.mwo_accesspanel', 'U') IS NOT NULL DROP TABLE dbo.mwo_accesspanel;
CREATE TABLE [dbo].[mwo_accesspanel]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[username] [varchar](10) NOT NULL,
	[password] [varchar](MAX) NOT NULL,
	[access] [int] DEFAULT ((0)) NOT NULL,
	[ipaddress] [varchar](MAX) NOT NULL,
);

IF OBJECT_ID('dbo.mwo_accesspages', 'U') IS NOT NULL DROP TABLE dbo.mwo_accesspages;
CREATE TABLE [dbo].[mwo_accesspages]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[name] [varchar](MAX) NOT NULL,
	[access] [int] DEFAULT ((0)) NOT NULL,
	[blocked] [varchar](MAX) DEFAULT NULL,
);

IF OBJECT_ID('dbo.mwo_menus', 'U') IS NOT NULL DROP TABLE dbo.mwo_menus;
CREATE TABLE [dbo].[mwo_menus]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[name] [varchar](MAX) NOT NULL,
	[label] [varchar](MAX) DEFAULT NULL,
	[link] [varchar](MAX) NOT NULL,
	[parentid] [int] DEFAULT ((0)) NOT NULL,
	[status] [int] DEFAULT ((1)) NOT NULL,
);

IF OBJECT_ID('dbo.mwo_configs', 'U') IS NOT NULL DROP TABLE dbo.mwo_configs;
CREATE TABLE [dbo].[mwo_configs]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[name] [varchar](MAX) NOT NULL,
	[type] [varchar](MAX) DEFAULT NULL,
	[data] [varchar](MAX) NOT NULL,
);

IF OBJECT_ID('dbo.mwo_rankings', 'U') IS NOT NULL DROP TABLE dbo.mwo_rankings;
CREATE TABLE [dbo].[mwo_rankings]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[name] [varchar](MAX) NOT NULL,
	[database] [varchar](MAX) NOT NULL,
	[table] [varchar](MAX) NOT NULL,
	[column] [varchar](MAX) NOT NULL,
	[custom] [varchar](MAX) NOT NULL,
	[max] [int] NOT NULL,
	[link] [varchar](MAX) NOT NULL,
	[type] [varchar](MAX) DEFAULT NULL,
);

IF OBJECT_ID('dbo.mwo_rankings_home', 'U') IS NOT NULL DROP TABLE dbo.mwo_rankings_home;
CREATE TABLE [dbo].[mwo_rankings_home]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[name] [varchar](MAX) NOT NULL,
	[database] [varchar](MAX) NOT NULL,
	[table] [varchar](MAX) NOT NULL,
	[column] [varchar](MAX) NOT NULL,
	[max] [int] NOT NULL,
	[custom] [varchar](MAX) DEFAULT NULL,
	[type] [varchar](MAX) DEFAULT NULL,
);

IF OBJECT_ID('dbo.mwo_news', 'U') IS NOT NULL DROP TABLE dbo.mwo_news;
CREATE TABLE [dbo].[mwo_news]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[title] [varchar](MAX) NOT NULL,
	[content] [varchar](MAX) NOT NULL,
	[image] [varchar](MAX) DEFAULT NULL,
	[tag] [varchar](MAX) DEFAULT NULL,
	[date] DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
);

IF OBJECT_ID('dbo.mwo_pages', 'U') IS NOT NULL DROP TABLE dbo.mwo_pages;
CREATE TABLE [dbo].[mwo_pages]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[title] [varchar](MAX) NOT NULL,
	[link] [varchar](MAX) NOT NULL,
	[content] [varchar](MAX) NOT NULL,
);

IF OBJECT_ID('dbo.mwo_events', 'U') IS NOT NULL DROP TABLE dbo.mwo_events;
CREATE TABLE [dbo].[mwo_events]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[name] [varchar](MAX) NOT NULL,
	[time] [varchar](MAX) NOT NULL,
);

IF OBJECT_ID('dbo.mwo_coins', 'U') IS NOT NULL DROP TABLE dbo.mwo_coins;
CREATE TABLE [dbo].[mwo_coins]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[name] [varchar](MAX) NOT NULL,
	[database] [varchar](MAX) NOT NULL,
	[table] [varchar](MAX) NOT NULL,
	[column] [varchar](MAX) NOT NULL,
	[price] [varchar](MAX) NOT NULL,
);

IF OBJECT_ID('dbo.mwo_vips', 'U') IS NOT NULL DROP TABLE dbo.mwo_vips;
CREATE TABLE [dbo].[mwo_vips]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[name] [varchar](MAX) NOT NULL,
	[database] [varchar](MAX) NOT NULL,
	[table] [varchar](MAX) NOT NULL,
	[column_level] [varchar](MAX) NOT NULL,
	[column_days] [varchar](MAX) NOT NULL,
	[level] [varchar](MAX) NOT NULL,
	[prices] [varchar](MAX) NOT NULL,
	[days] [varchar](MAX) NOT NULL,
);

IF OBJECT_ID('dbo.mwo_tickets', 'U') IS NOT NULL DROP TABLE dbo.mwo_tickets;
CREATE TABLE [dbo].[mwo_tickets]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[subject] [varchar](300) NOT NULL,
	[message] [varchar](MAX) NOT NULL,
	[username] [varchar](10) NOT NULL,
	[date] DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	[image] [varchar](MAX) NOT NULL,
);

IF OBJECT_ID('dbo.mwo_tickets_answers', 'U') IS NOT NULL DROP TABLE dbo.mwo_tickets_answers;
CREATE TABLE [dbo].[mwo_tickets_answers]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[message] [varchar](MAX) NOT NULL,
	[username] [varchar](10) NOT NULL,
	[ticket_id] [int] NOT NULL,
	[date] DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
);

IF OBJECT_ID('dbo.mwo_slides', 'U') IS NOT NULL DROP TABLE dbo.mwo_slides;
CREATE TABLE [dbo].[mwo_slides]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[name] [varchar](MAX) NOT NULL,
	[label] [varchar](MAX) DEFAULT NULL,
	[link] [varchar](MAX) NOT NULL,
	[image] [varchar](MAX) NOT NULL,
	[status] [int] DEFAULT ((1)) NOT NULL,
);

IF OBJECT_ID('dbo.mwo_kingofmu', 'U') IS NOT NULL DROP TABLE dbo.mwo_kingofmu;
CREATE TABLE [dbo].[mwo_kingofmu]
(
	[active] [int] DEFAULT ((1)) NOT NULL,
	[database] [varchar](MAX) NOT NULL,
	[table] [varchar](MAX) NOT NULL,
	[mode] [varchar](MAX) NOT NULL,
	[custom] [varchar](MAX) DEFAULT NULL,
	[orderby] [varchar](MAX) DEFAULT NULL,
	[character] [varchar](MAX) DEFAULT NULL,
	[wins] [int] DEFAULT ((0)) NOT NULL,
);

IF OBJECT_ID('dbo.mwo_customers', 'U') IS NOT NULL DROP TABLE dbo.mwo_customers;
CREATE TABLE [dbo].[mwo_customers]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[memb___id] [varchar](10) NOT NULL,
	[name] [varchar](MAX) NOT NULL,
	[email] [varchar](MAX) NOT NULL,
	[cpf] [varchar](11) NOT NULL,
	[street] [varchar](MAX) NOT NULL,
	[number] [varchar](10) NOT NULL,
	[complement] [varchar](MAX) DEFAULT NULL,
	[district] [varchar](MAX) NOT NULL,
	[city] [varchar](MAX) NOT NULL,
	[state] [varchar](MAX) NOT NULL,
	[postalcode] [varchar](9) NOT NULL,
);

IF OBJECT_ID('dbo.mwo_items_ancients', 'U') IS NOT NULL DROP TABLE dbo.mwo_items_ancients;
CREATE TABLE [dbo].[mwo_items_ancients]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[section] [int] NOT NULL,
	[index_] [int] NOT NULL,
	[ancient] [int] NOT NULL,
	[name] [varchar](MAX) NOT NULL,
);

IF OBJECT_ID('dbo.mwo_items_jewelofharmony', 'U') IS NOT NULL DROP TABLE dbo.mwo_items_jewelofharmony;
CREATE TABLE [dbo].[mwo_items_jewelofharmony]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[section] [int] NOT NULL,
	[index_] [int] NOT NULL,
	[level] [int] NOT NULL,
	[harmonys] [varchar](MAX) NOT NULL,
);

IF OBJECT_ID('dbo.mwo_items_sockets', 'U') IS NOT NULL DROP TABLE dbo.mwo_items_sockets;
CREATE TABLE [dbo].[mwo_items_sockets]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[section] [int] NOT NULL,
	[index_] [int] NOT NULL,
	[max] [int] NOT NULL,
	[sockets] [varchar](MAX) NOT NULL,
);

IF OBJECT_ID('dbo.mwo_items_refines', 'U') IS NOT NULL DROP TABLE dbo.mwo_items_refines;
CREATE TABLE [dbo].[mwo_items_refines]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[section] [int] NOT NULL,
	[index_] [int] NOT NULL,
	[options] [varchar](MAX) NOT NULL,
);

IF OBJECT_ID('dbo.mwo_items_options', 'U') IS NOT NULL DROP TABLE dbo.mwo_items_options;
CREATE TABLE [dbo].[mwo_items_options]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[index_] [int] NOT NULL,
	[optionindex] [int] NOT NULL,
	[value] [int] NOT NULL,
	[minrange] [int] NOT NULL,
	[maxrange] [int] NOT NULL,
	[skill] [varchar](1) NOT NULL,
	[luck] [varchar](1) NOT NULL,
	[option] [varchar](1) NOT NULL,
	[newoption] [varchar](1) NOT NULL,
	[name] [varchar](MAX) DEFAULT NULL,
);

IF OBJECT_ID('dbo.mwo_skills_name', 'U') IS NOT NULL DROP TABLE dbo.mwo_skills_name;
CREATE TABLE [dbo].[mwo_skills_name]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[index_] [int] NOT NULL,
	[name] [varchar](MAX) NOT NULL,
);

IF OBJECT_ID('dbo.mwo_webshops', 'U') IS NOT NULL DROP TABLE dbo.mwo_webshops;
create table mwo_webshops
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[name] [varchar](MAX) NOT NULL,
	[label] [varchar](MAX) DEFAULT NULL,
	[link] [varchar](MAX) NOT NULL,
	[parentid] [int] DEFAULT ((0)) NOT NULL,
	[status] [int] DEFAULT ((1)) NOT NULL,
	[coin] [int] DEFAULT ((1)) NOT NULL,
);

IF OBJECT_ID('dbo.mwo_webshop_categories', 'U') IS NOT NULL DROP TABLE dbo.mwo_webshop_categories;
CREATE TABLE [dbo].[mwo_webshop_categories]
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[name] [varchar](MAX) NOT NULL,
	[label] [varchar](MAX) DEFAULT NULL,
	[link] [varchar](MAX) NOT NULL,
	[parentid] [int] DEFAULT ((0)) NOT NULL,
	[webshopid] [int] DEFAULT ((0)) NOT NULL,
	[status] [int] DEFAULT ((1)) NOT NULL,
);

IF OBJECT_ID('dbo.mwo_webshop_items', 'U') IS NOT NULL DROP TABLE dbo.mwo_webshop_items;
create table mwo_webshop_items
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[categoryid] [int] NOT NULL,
	[section] [int] NOT NULL,
	[index_] [int] NOT NULL,
	[name] [varchar](MAX) NOT NULL,
	[durability] [int] DEFAULT ((255)) NOT NULL,
	[width] [int] DEFAULT ((255)) NOT NULL,
	[height] [int] DEFAULT ((255)) NOT NULL,
	[skill] [int] DEFAULT ((0)) NOT NULL,
	[link] [varchar](MAX) NOT NULL,
	[status] [int] DEFAULT ((1)) NOT NULL,
	[price] [int] DEFAULT ((0)) NOT NULL,
	[price_level] [int] DEFAULT ((0)) NOT NULL,
	[price_option] [int] DEFAULT ((0)) NOT NULL,
	[price_skill] [int] DEFAULT ((0)) NOT NULL,
	[price_luck] [int] DEFAULT ((0)) NOT NULL,
	[price_ancient] [int] DEFAULT ((0)) NOT NULL,
	[price_harmony] [int] DEFAULT ((0)) NOT NULL,
	[price_refine] [int] DEFAULT ((0)) NOT NULL,
	[price_socket] [int] DEFAULT ((0)) NOT NULL,
	[price_excellent] [int] DEFAULT ((0)) NOT NULL,
	[max_excellent] [int] DEFAULT ((6)) NOT NULL,
	[max_sockets] [int] DEFAULT ((5)) NOT NULL,
	[image] [varchar](MAX) DEFAULT NULL,
	[classes] [varchar](MAX) DEFAULT NULL,
);

IF OBJECT_ID('dbo.mwo_webshop_orders', 'U') IS NOT NULL DROP TABLE dbo.mwo_webshop_orders;
create table mwo_webshop_orders
(
	[ID] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[username] [varchar](10) NOT NULL,
	[coin] [varchar](MAX) NOT NULL,
	[section] [int] NOT NULL,
	[index_] [int] NOT NULL,
	[serial] [varchar](MAX) NOT NULL,
	[date] DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

IF OBJECT_ID('dbo.mwo_castlesiege', 'U') IS NOT NULL DROP TABLE dbo.mwo_castlesiege;
CREATE TABLE [dbo].[mwo_castlesiege]
(
	[active] [int] DEFAULT ((1)) NOT NULL,
	[mode] [varchar](MAX) NOT NULL,
	[confrontation] [varchar](MAX) NOT NULL,
	[guild] [varchar](MAX) NOT NULL
);

-- Insert rows into table 'mwo_kingofmu'
INSERT INTO mwo_kingofmu
	( -- columns to insert data into
	[active], [database], [table], [mode], [character], [wins]
	)
VALUES
	( -- first row: values for the columns in the list above
		0, 'MuOnline', 'Character', 'manual', 'MuWebOnline', 1
);

-- Insert rows into table 'mwo_castlesiege'
INSERT INTO mwo_castlesiege
	( -- columns to insert data into
	[active], [mode], [confrontation], [guild]
	)
VALUES
	( -- first row: values for the columns in the list above
		0, 'manual', 'Sábado 18:00h', 'MuWebOnline'
);


ALTER TABLE dbo.MEMB_INFO ADD [mwo_credits] [int] NOT NULL DEFAULT 0;
ALTER TABLE dbo.MEMB_INFO ADD [mwo_token] [varchar](MAX)  DEFAULT NULL;
ALTER TABLE dbo.Character ADD [mwo_image] [varchar](MAX)  DEFAULT NULL;

-- Insert rows into table 'mwo_configs'
INSERT INTO mwo_configs
	( -- columns to insert data into
	[name], [type], [data]
	)
VALUES
	( -- first row: values for the columns in the list above
		'Detalhes', 'details', '[{"name":"SITE_TITLE","label":"T\u00edtulo do site","value":"MuWebOnline"},{"name":"SITE_SIGLA","label":"Sigla do servidor","value":"MWO"},{"name":"SERVER_NAME","label":"Nome do servidor","value":"MuWebOnline"},{"name":"SERVER_SLOGAN","label":"Slogan do servidor","value":"Fazendo a diferen\u00e7a"},{"name":"SERVER_VERSION","label":"Vers\u00e3o do servidor","value":"1.05D Season 6"},{"name":"SERVER_DROP","label":"Drop do servidor","value":"35%"},{"name":"SERVER_XP","label":"XP do servidor","value":"50x~150x"},{"name":"SERVER_BUGBLESS","label":"Bugbless status","value":"Offline"}]'
),
	( -- first row: values for the columns in the list above
		'Templates', 'templates', '[{"name":"TEMPLATE_SITE","label":"Template do site","value":"default"},{"name":"TEMPLATE_ADMIN","label":"Template do painel admin","value":"gentelella"},{"name":"TEMPLATE_EMAILS","label":"Template dos emails","value":"emails"},{"name":"TEMPLATE_CACHE","label":"Ativar cache dos templates","value":"false"},{"name":"TEMPLATE_DEBUG","label":"Ativar errors dos templates","value":"true"}]'
),
	( -- first row: values for the columns in the list above
		'reCaptcha', 'captcha', '[{"name":"CAPTCHA_SECRET","label":"Chave Secreta do Captcha","value":"secret key"},{"name":"CAPTCHA_SITEKEY","label":"Chave Site do Captcha","value":"site key"}]'
),
	( -- first row: values for the columns in the list above
		'Cadastro', 'register', '[{"name":"EMAIL_ACTIVE","label":"Ativa\u00e7\u00e3o pro email","value":"false"},{"name":"FORCELOWER","label":"For\u00e7a o login a ser minusculo.","value":"false"},{"name":"BONUS_VIP_ACTIVE","label":"Ativa b\u00f4nus de vip no cadastro","value":"true"},{"name":"BONUS_VIP_LEVEL","label":"Level do vip","value":"1"},{"name":"BONUS_VIP_DAYS","label":"Quatidades de dias de vip","value":"30"},{"name":"EMAIL_REPEAT","label":"Pode cadastrar com o mesmo email duas vezes","value":"false"},{"name":"BONUS_CREDITS_ACTIVE","label":"Ativar b\u00f4nus de cr\u00e9ditos","value":"false"},{"name":"BONUS_CREDITS_AMOUNT","label":"Quantidade de cr\u00e9ditos","value":"0"}]'
),
	( -- first row: values for the columns in the list above
		'Email', 'email', '[{"name":"HOSTEMAIL","label":"Servidor de email","value":"mail.meudominio.com"},{"name":"AUTHSMTP","label":"Precisa de usu\u00e1rio e Senha Para login","value":"true"},{"name":"SITEEMAIL","label":"Email usado para envio e conex\u00e3o SMTP","value":"meuemail@meudominio.com"},{"name":"PASSEMAIL","label":"Senha do email para conex\u00e3o SMTP","value":"minhasenha"},{"name":"SECUREEMAIL","label":"Tipo de seguran\u00e7a ex.: ssl","value":"ssl"},{"name":"PORTSMTP","label":"Porta de conex\u00e3o SMTP","value":"465"},{"name":"CHARSETEMAIL","label":"Linguagem do Email","value":"UTF-8"}]'
),
	( -- first row: values for the columns in the list above
		'Colunas', 'columns', '[{"name":"COLUMN_RESETS","label":"Coluna de resets","value":"Resets"},{"name":"COLUMN_RESETS_DAY","label":"Coluna de resets di\u00e1rio","value":"ResetsDay"},{"name":"COLUMN_RESETS_WEEK","label":"Coluna de resets semanal","value":"ResetsWeek"},{"name":"COLUMN_RESETS_MONTH","label":"Coluna de resets mensal","value":"ResetsMonth"},{"name":"COLUMN_MASTER_RESETS","label":"Coluna de master reset","value":"MasterResets"}]'
),
	( -- first row: values for the columns in the list above
		'MuServer', 'muserver', '[{"name":"MD5","label":"Ativar MD5","value":"false"},{"name":"VI_CURR_INFO","label":"Joinserver com sistema de idade","value":"false"},{"name":"VESION_MUSERVER","label":"Vers\u00e3o do MuServer","value":"0"},{"name":"MUSERVER_TEAM","label":"Desenvolvedor do MuServer","value":"0"}]'
),
	( -- first row: values for the columns in the list above
		'Classes', 'classcodes', '[{"name":"DW","label":"Dark Wizard","value":0},{"name":"SM","label":"Soul Master","value":1},{"name":"GM","label":"Grand Master","value":2},{"name":"DK","label":"Dark Knight","value":16},{"name":"BK","label":"Blade Knight","value":17},{"name":"BM","label":"Blade Master","value":18},{"name":"FE","label":"Fairy Elf","value":32},{"name":"ME","label":"Muse Elf","value":33},{"name":"HE","label":"High Elf","value":34},{"name":"MG","label":"Magic Gladiator","value":48},{"name":"DMM","label":"Duel Master","value":49},{"name":"DL","label":"Dark Lord","value":64},{"name":"LE","label":"Lord Emperor","value":65},{"name":"SU","label":"Summoner","value":80},{"name":"BS","label":"Blood Summoner","value":81},{"name":"DMS","label":"Dimension Master","value":82},{"name":"RF","label":"Rage Fighter","value":96},{"name":"FM","label":"Fist Master","value":98}]'
),
	( -- first row: values for the columns in the list above
		'Reset', 'reset', '[{"name":"LIMIT_RESETS","label":"Limite de resets","value":"0,0,0,0"},{"name":"LEVEL_RESETS","label":"Level necess\u00e1rio para resetar","value":"400,380,360,340"},{"name":"LEVEL_AFTER","label":"Level ap\u00f3s resetar","value":"0,1,2,3"},{"name":"ZEN_REQUIRE","label":"Precisa de X zen para resetar","value":"0,0,0,0"},{"name":"POINTS","label":"Ganha X pontos por reset","value":"0,0,0,0"},{"name":"CLEAR_ITENS","label":"Reseta os itens ao resetar","value":"false,false,false,false"},{"name":"CLEAR_MAGICS","label":"Reseta as magias ao resetar","value":"false,false,false,false"},{"name":"CLEAR_QUESTS","label":"Reseta as quests ao resetar","value":"false,false,false,false"}]'
),
	( -- first row: values for the columns in the list above
		'Master Reset', 'masterreset', '[{"name":"LIMIT_RESETS","label":"Limite de master resets","value":"0,0,0,0"},{"name":"LEVEL_RESETS","label":"Level necess\u00e1rio para master reset","value":"400,380,360,340"},{"name":"LEVEL_AFTER","label":"Level ap\u00f3s master reset","value":"0,1,2,3"},{"name":"ZEN_REQUIRE","label":"Precisa de X zen para master reset","value":"0,0,0,0"},{"name":"POINTS","label":"Ganha X pontos por master reset","value":"0,0,0,0"},{"name":"CLEAR_ITENS","label":"Reseta os itens ao master reset","value":"false,false,false,false"},{"name":"CLEAR_MAGICS","label":"Reseta as magias ao master reset","value":"false,false,false,false"},{"name":"CLEAR_QUESTS","label":"Reseta as quests ao master reset","value":"false,false,false,false"},{"name":"RESETS_REQUIRE","label":"Resets necess\u00e1rios para master reset","value":"1000,980,960,940"}]'
),
	( -- first row: values for the columns in the list above
		'Limpar PK', 'cleanpk', '[{"name":"CLEAN_MODE","label":"Modo de cobran\u00e7a zen","value":1},{"name":"PRICEZEN","label":"Pre\u00e7o zen por vip","value":"2000000, 1500000, 1000000, 1000000"}]'
),
	( -- first row: values for the columns in the list above
		'Alterar Nome', 'changenick', '[{"name":"PRICEZEN","label":"Pre\u00e7o zen por vip","value":"2000000, 1500000, 1000000, 1000000"},{"name":"BLOCKED_NAMES","label":"Nomes Bloqueados","value":"WEBZEN, ADM, GM, MD, NT, DV"}]'
),
	( -- first row: values for the columns in the list above
		'Alterar Classe', 'changeclass', '[{"name":"PRICEZEN","label":"Pre\u00e7o zen por vip","value":"2000000, 1500000, 1000000, 1000000"},{"name":"RESET_QUESTS","label":"Resetar Quest ao trocar de classe","value":"true"},{"name":"RESET_SKILLS","label":"Resetar Skills ao trocar de classe","value":"true"}]'
),
	( -- first row: values for the columns in the list above
		'Alterar Imagem', 'changeimage', '[{"name":"PRICEZEN","label":"Pre\u00e7o zen por vip","value":"2000000, 1500000, 1000000, 1000000"}]'
),
	( -- first row: values for the columns in the list above
		'Social Links', 'sociallinks', '[{"name":"FACEBOOK","label":"Facebook","value":"Meu Facebook"},{"name":"TWITTER","label":"Twitter","value":"Meu Twitter"},{"name":"INSTAGRAM","label":"Instagram","value":"Meu Instagram"},{"name":"DISCORD","label":"Discord","value":"Meu Discord"},{"name":"YOUTUBE","label":"YouTube","value":"Meu YouTube"},{"name":"WHATSAPP","label":"WhastApp","value":"Meu WhastApp"},{"name":"TEAMSPEAK","label":"TeamSpeak","value":"Meu TeamSpeak"}]'
),
	( -- first row: values for the columns in the list above
		'API MWOPay', 'apimwopay', '[{"name":"EMAIL","label":"Email","value":"Meu Email"},{"name":"TOKEN","label":"Token","value":"Meu Token"}]'
);