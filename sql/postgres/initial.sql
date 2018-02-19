drop table if exists catalogs;
create table catalogs (
  version integer not null primary key,
  records integer not null default 0,
  inserted integer not null default 0,
  updated integer not null default 0,
  deleted integer not null default 0
);

drop table if exists rfcs;
create table rfcs (
  rfc varchar(17) not null primary key,
  sncf bool not null default false,
  sub bool not null default false,
  since integer not null default 0,
  deleted bool not null default false
);

drop table if exists rfclogs;
create table rfclogs (
  version integer not null,
  rfc varchar(17) not null,
  action smallint not null /* smallint values goes from (-32768 to +32767) */
);
