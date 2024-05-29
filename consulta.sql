conciliacao

Data
Historico
Nota
Débito
Crédito


CREATE TABLE conciliacao
(
  conc_id serial NOT NULL,
  conc_data date,
  conc_historico character varying(200) NOT NULL,
  conc_nota integer NOT NULL,
  conc_debito numeric NOT NULL,
  conc_credito numeric NOT NULL,
  data_hora_inc timestamp without time zone NOT NULL DEFAULT now(),
  data_hora_alt timestamp without time zone,
  CONSTRAINT pk_conciliacao PRIMARY KEY (conc_id)
)
