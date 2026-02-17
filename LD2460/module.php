<?php

    class LD2460 extends IPSModule
    {
        
        public function Create() {
            //Never delete this line!
            parent::Create();


            
            $this->RegisterPropertyInteger('Targets', 1);
            $this->RegisterPropertyInteger('Counter1', 1);
            $this->RegisterPropertyInteger('Counter2', 1);


            // Visualisierungstyp auf 1 setzen, da wir HTML anbieten möchten
            $this->SetVisualizationType(1);
        }

        public function ApplyChanges() {
            parent::ApplyChanges();

            // Aktualisiere registrierte Nachrichten
            foreach ($this->GetMessageList() as $senderID => $messageIDs) {
                foreach($messageIDs as $messageID) {
                    $this->UnregisterMessage($senderID, $messageID);
                }
            }

            foreach(['Counter1', 'Counter2'] as $counterProperty) {
                $this->RegisterMessage($this->ReadPropertyInteger($counterProperty), OM_CHANGENAME);
                $this->RegisterMessage($this->ReadPropertyInteger($counterProperty), VM_UPDATE);
            }

            // Schicke eine komplette Update-Nachricht an die Darstellung, da sich ja Parameter geändert haben können
            $this->UpdateVisualizationValue($this->GetFullUpdateMessage());
        }

        public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
            // Man könnte noch auf weitere Nachrichten reagieren, um das ganze "vollständig" zu machen
            // Werden registrierte Objekte gelöscht? Aktualisiert sich das Bild? Da dies aber nur ein Beispiel ist, lasse ich diese Nachrichten weg
            foreach(['Counter1', 'Counter2'] as $index => $counterProperty) {
                if ($SenderID === $this->ReadPropertyInteger($counterProperty)) {
                    switch ($Message) {
                        case OM_CHANGENAME:
                            // Teile der HTML-Darstellung den neuen Namen mit
                            $this->UpdateVisualizationValue(json_encode([
                                'name' . ($index + 1) => $Data[0]
                            ]));
                            break;

                        case VM_UPDATE:
                            // Teile der HTML-Darstellung den neuen Wert mit. Damit dieser korrekt formatiert ist, holen wir uns den von der Variablen via GetValueFormatted
                          //  $this->UpdateVisualizationValue(json_encode([
                          //    'value' . ($index + 1) => GetValueFormatted($this->ReadPropertyInteger($counterProperty))
                          //  ]));

                            // Schicke eine komplette Update-Nachricht an die Darstellung, da sich ja Parameter geändert haben können
                            $this->UpdateVisualizationValue($this->GetFullUpdateMessage());
                            break;
                    }
                }
            }
        }

        
        public function GetVisualizationTile() {
            // Füge ein Skript hinzu, um beim laden, analog zu Änderungen bei Laufzeit, die Werte zu setzen
            // Obwohl die Rückgabe von GetFullUpdateMessage ja schon JSON-codiert ist wird dennoch ein weiteres mal json_encode ausgeführt
            // Damit wird dem String Anführungszeichen hinzugefügt und eventuelle Anführungszeichen innerhalb werden korrekt escaped
            $initialHandling = '<script>handleMessage(' . json_encode($this->GetFullUpdateMessage()) . ');</script>';

            // Füge statisches HTML aus Datei hinzu
            $module = file_get_contents(__DIR__ . '/module.html');

            // Gebe alles zurück. 
            // Wichtig: $initialHandling nach hinten, da die Funktion handleMessage ja erst im HTML definiert wird
            return $module . $initialHandling;
        }

        // Generiere eine Nachricht, die alle Elemente in der HTML-Darstellung aktualisiert
        private function GetFullUpdateMessage() {
            $targetsID = $this->ReadPropertyInteger('Targets');
            $counter1ID = $this->ReadPropertyInteger('Counter1');
            $counter2ID = $this->ReadPropertyInteger('Counter2');
            $targetsExsists = IPS_VariableExists($targetsID);   
            $counter1Exists = IPS_VariableExists($counter1ID);
            $counter2Exists = IPS_VariableExists($counter2ID);
            $result = [
                'counter1' => $counter1Exists,
                'counter2' => $counter2Exists,
                'targets' => $targetsExsists
            ];
            if ($counter1Exists) {
                $result['name1'] = IPS_GetName($counter1ID);
                $result['value1'] = GetValueFormatted($counter1ID);
            }
            if ($targetsExsists) {
                $result['nameTargets'] = IPS_GetName($targetsID);
                $result['valueTargets'] = GetValueFormatted($targetsID);
            }   
            if ($counter2Exists) {
                $result['name2'] = IPS_GetName($counter2ID);
                $result['value2'] = GetValueFormatted($counter2ID);
            }

            return json_encode($result);
        }
    
    }

?>
