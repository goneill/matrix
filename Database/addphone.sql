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
	"9178541604",
	3,
	"Kevin Walker iPhone4",
	"KW-iP4",
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
	"9178081374",
	1,
	"Kevin Walker iPhone6",
	"KW-iP6",
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
	"2032930277",
	1,
	"Kevin Walker Google",
	"KW-G",
	"green-pushpin.png",
	now(), 
	now()	
);
