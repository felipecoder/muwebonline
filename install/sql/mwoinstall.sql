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
	[custom] [varchar](MAX) DEFAULT NULL,
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

ALTER TABLE dbo.MEMB_INFO ADD [mwo_credits] [int] NOT NULL DEFAULT 0;
ALTER TABLE dbo.MEMB_INFO ADD [mwo_token] [varchar](MAX)  DEFAULT NULL;

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
		'Templates', 'templates', '[{"name":"TEMPLATE_SITE","label":"Template do site","value":"youplay"},{"name":"TEMPLATE_ADMIN","label":"Template do painel admin","value":"adminlte"},{"name":"TEMPLATE_EMAILS","label":"Template dos emails","value":"emails"},{"name":"TEMPLATE_CACHE","label":"Ativar cache dos templates","value":"false"},{"name":"TEMPLATE_DEBUG","label":"Ativar errors dos templates","value":"true"}]'
),
	( -- first row: values for the columns in the list above
		'reCaptcha', 'captcha', '[{"name":"CAPTCHA_SECRET","label":"Chave Secreta do Captcha","value":"6LcZ9a8UAAAAAHIXoLjkZbQJV8Z8WccjgxP2WKgz"},{"name":"CAPTCHA_SITEKEY","label":"Chave Site do Captcha","value":"6LcZ9a8UAAAAAOZaYkSorl0jJ2P7YckwLMP_eaFx"}]'
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
);