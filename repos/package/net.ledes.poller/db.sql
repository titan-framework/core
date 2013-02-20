CREATE TABLE poller (
  id SERIAL, 
  answer VARCHAR(256), 
  _graphic VARCHAR(32) NOT NULL, 
  _user INTEGER NOT NULL, 
  _update TIMESTAMP WITHOUT TIME ZONE DEFAULT now() NOT NULL, 
  _create TIMESTAMP WITHOUT TIME ZONE DEFAULT now() NOT NULL, 
  CONSTRAINT poller_pkey PRIMARY KEY(id), 
  CONSTRAINT poller_user_fk FOREIGN KEY (_user)
    REFERENCES _user(_id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;

CREATE TABLE poller_answer (
  _poller INTEGER NOT NULL, 
  _order INTEGER NOT NULL, 
  _label VARCHAR(128), 
  _votes INTEGER DEFAULT 0 NOT NULL, 
  CONSTRAINT poller_answer_idx PRIMARY KEY("_poller", "_order"), 
  CONSTRAINT poller_answer_fk FOREIGN KEY ("_poller")
    REFERENCES poller(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;

