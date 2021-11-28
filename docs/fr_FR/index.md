# Plugin Fibaro pour Jeedom


![fibaro_icon](https://github.com/rems02/fibaro/raw/master/docs/fr_FR/fibaro_icon.png)

Description 
===

Ce plugin très puissant et très complet vous permettra de gérer 
et interagir avec les modules de vos boxes Fibaro HC2 / HC3 ou HCL.
Le type de modules est détecté automatiquement puis inséré dans Jeedom.
Les deux boxes communiques facilement et le changement d'état est presque instantané
grace au script LUA pour la HC2 et HC3 ou un scenario pour la HCL.
L'ajout de modules est régulier, leurs intégration peut être ajoutée sur une simple demande.


_Modules compatible:_
- interrupteur On/Off (état / on / off)
- Wall Plug Fibaro (état / on / off)
- Wall Plug NodOn (état / on / off)
- Capteurs de températures (état)
- Volet roulant  (état / Open / close / stop / slider)
- Variateurs / dimmers (état / On / Off / slider)
- Détecteurs d'ouvertures (état ouverture  / état armement)
- Détecteurs de mouvements (état mouvement  / état armement)
- Détecteurs de mouvements SATEL (état mouvement / état armement)
- Détecteurs de mouvements l'œil de Sauron v2 (état mouvement / état armement)
- Détecteurs d'humidité (état)
- Luxmètres (état)
- Détecteur de Fumé (FGSS001)
- Détecteur d'inodations (FGFS101)

Configuration
===

Dans la partie _Configuration_ Renseignez l'IP, login et mot de passe de votre box (HC2 ou HCL)

![ip_login_password](https://github.com/rems02/fibaro/raw/master/docs/fr_FR/ip_login_password.PNG)

Cliquez sur bouton **Ajouter**

![add_devices](https://github.com/rems02/fibaro/raw/master/docs/fr_FR/add_devices.PNG)

Renseignez bien **ID** de votre module que vous souhaitez d'ajouter

![ID_Device](https://github.com/rems02/fibaro/raw/master/docs/fr_FR/ID_Device.PNG)

Retour d'information Fibaro->Jeedom
===


HC3
---

Pour Rafraîchir des modules et leurs passer l'information de changement d'état il faut creer une scene dans votre HC3

**_Code LUA - DECLARATIONS_**

```
{
	operator = "any",
	conditions = {
		{
			type = "device",
			id = 48,
			property = "value",
			operator = "anyValue",
			value = true,
			isTrigger = true
		},
		{
			type = "device",
			id = 52,
			property = "value",
			operator = "anyValue",
			value = true,
			isTrigger = true
		}
	}
}
```


**_Code LUA - ACTION_**

```
---- Paramètrage utilisateur ----

-- Associations [ID Fibaro] = ID Jeedom
local HC2Jeedom = {
--ID_HC2 = ID_Jeedom, 
  [48]=2004, --Lumière Cuisine 
  [52] =2122  --Overture Salon  
}

IP_Jeedom = "192.168.1.1" -- IP Jeedom
apiKeyJeedom = "45Gfgggf254ds;jfklsdf24646s4dfg" -- API key Jeedom
---- Fin de paramètrage utilisateur ----


--- /!\ Ne rien modifier a partir d'ici /!\ ---
local trigger = sourceTrigger

--Construction de URL
local http = net.HTTPClient()
local url = "http://" ..IP_Jeedom .."/core/api/jeeApi.php?apikey=" ..apiKeyJeedom .."&type=cmd&id=" .. HC2Jeedom[trigger.id]



if (tri?gger.property == 'property') then
  --fibaro:debug('Fibaro ID = ' .. trigger.id)
  --fibaro:debug('Jeedom ID = ' .. HC2Jeedom[trigger.id])
  --fibaro:debug(url)

  http:request(url, {
    success = function(response)
    if response.status == 200 then
        fibaro:debug('OK, réponse : '.. response.data)
    else
        fibaro:debug("Erreur : status=" .. tostring(response.status))
    end
end,
error = function(err)
    fibaro:debug("Erreur : " .. err)
end,
options = {
    method = 'GET'
}
}) 

end
```


HC2
---

Pour Rafraîchir des modules et leurs passer l'information de changement d'état il faut creer une scene dans votre HC2

**_Code LUA_**

    --[[
    %% properties
    221 value
    197 value
    665 value
    382 value
    382 armed
    22 value
    22 armed
    --]]
    
    ---- Paramètrage utilisateur ----
    
    -- Associations [ID Fibaro] = ID Jeedom
    local HC2Jeedom = {
    --ID_HC2 = ID_Jeedom, 
      [197]=2004, --Lumière Cuisine 
      [221]=2005, --Lumière Salon
      [665]=2062, --Température Porte Cuisine
      [382]=2128, --Mouvement Mezzanine /!\ ajouter 382 value et 382 armed  ds properties
      [22] =2122  --Overture Salon /!\ ajouter 22 value et 22 armed  ds properties  
    }
    
    IP_Jeedom = "192.168.1.101" -- IP Jeedom
    apiKeyJeedom = "45Gfgggf254ds;jfklsdf24646s4dfg" -- API key Jeedom
    ---- Fin de paramètrage utilisateur ----
    
    --- /!\ Ne rien modifier a partir d'ici /!\ ---
    local trigger = fibaro:getSourceTrigger();
    
    --Construction de URL
    local http = net.HTTPClient()
    local url = "http://" ..IP_Jeedom .."/core/api/jeeApi.php?apikey=" ..apiKeyJeedom .."&type=cmd&id=" ..HC2Jeedom[trigger['deviceID']]
    
    
    
    if (trigger['type'] == 'property') then
      --fibaro:debug('Fibaro ID = ' .. trigger['deviceID']);
      --fibaro:debug('Jeedom ID = ' .. HC2Jeedom[trigger['deviceID']]);
      --fibaro:debug(url)
      
      http:request(url, {
    	success = function(response)
		if response.status == 200 then
			fibaro:debug('OK, réponse : '.. response.data)
		else
			fibaro:debug("Erreur : status=" .. tostring(response.status))
		end
	end,
	error = function(err)
		fibaro:debug("Erreur : " .. err)
	end,
	options = {
		method = 'GET'
	}
    }) 

    end


HCL
---
Voilà la procédure de création d'un scénario dans votre Jeedom

Dans Outil scénario:.

![Ajouter](https://github.com/rems02/fibaro/raw/master/docs/fr_FR/1.PNG)

![Nom](https://github.com/rems02/fibaro/raw/master/docs/fr_FR/2.PNG)

Sélectionner **Programmé** puis **+** puis ajouter * * * * **

![déclencheur](https://github.com/rems02/fibaro/raw/master/docs/fr_FR/3.PNG)

![scenario](https://github.com/rems02/fibaro/raw/master/docs/fr_FR/4.PNG)

![ajouter bloc](https://github.com/rems02/fibaro/raw/master/docs/fr_FR/5.PNG)

![action](https://github.com/rems02/fibaro/raw/master/docs/fr_FR/6.PNG)

![ajouter](https://github.com/rems02/fibaro/raw/master/docs/fr_FR/7.PNG)

a partir d'ici vous pouvez répéter la procédure tant de fois que vous avez de modules a importer

![action](https://github.com/rems02/fibaro/raw/master/docs/fr_FR/8.PNG)

![valider](https://github.com/rems02/fibaro/raw/master/docs/fr_FR/9.PNG)

Changelog
===
[Le Changelog se trouve ici](https://rems02.github.io/fibaro/fr_FR/changelog)
