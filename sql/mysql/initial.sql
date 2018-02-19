drop table if exists versions;
create table catalogs (
  version int unsigned not null primary key,
  records int unsigned not null default 0,
  inserted int unsigned not null default 0,
  updated int unsigned not null default 0,
  deleted int unsigned not null default 0
) engine=MyISAM;

drop table if exists rfcs;
create table rfcs (
  rfc varchar(17) not null primary key,
  sncf bool not null default false,
  sub bool not null default false,
  since int unsigned not null default 0,
  deleted bool not null default false
) engine=MyISAM;

drop table if exists rfclogs;
create table rfclogs (
  version int unsigned not null,
  rfc varchar(17) not null,
  action tinyint(1) unsigned not null /* tinyint(1) values goes from (0-255) */
) engine=MyISAM;
