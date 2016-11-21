#add phone
use matrix;

INSERT INTO phones ( 
	CaseID,
	PhoneNumber,
	ServiceProviderID,
	ShortName,
	LongName,
	Icon,
	Created,
	Modified
) VALUES (
	1,  
	"3474446552",
	1,
	"Frank Jenkins",
	"FJ",
	"red-pushpin.png",
	now(),
	now()
);

INSERT INTO phones 	(
	CaseID,
	PhoneNumber,
	ServiceProviderID,
	ShortName,
	LongName,
	Icon,
	Created ,
	Modified
) VALUES (
	1,  
	"6462417067",
	1,
	"Cory Harris",
	"CH",
	"blue-pushpin.png",
	now(), 
	now()
);

INSERT INTO phones (
	CaseID,
	PhoneNumber,
	ServiceProviderID,
	ShortName,
	LongName,
	Icon,
	Created ,
	Modified
) VALUES (
	1,  
	"3479931973",
	1,
	"UNK 1",
	"UN1",
	"green-pushpin.png",
	now(), 
	now()	
);
