drop table if exists catalogs;
create table catalogs (
  version int primary key not null,
  records int not null default 0,
  inserted int not null default 0,
  updated int not null default 0,
  deleted int not null default 0
);

drop table if exists rfcs;
create table rfcs (
  rfc text primary key not null,
  sncf int not null default 0,
  sub int not null default 0,
  since int not null,
  deleted int not null default 0
);

drop table if exists rfclogs;
create table rfclogs (
  version int not null,
  rfc text not null,
  action int not null
);
