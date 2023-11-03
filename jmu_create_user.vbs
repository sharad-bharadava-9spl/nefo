'Format a byte array to a hex string to be sent to the server.
Function OctetToHexStr(ByVal arrbytOctet)
    Dim k
    For k = 1 To Lenb(arrbytOctet)
        OctetToHexStr = OctetToHexStr _
          & Right("0" & Hex(Ascb(Midb(arrbytOctet, k, 1))), 2)
    Next
End Function

Function encodeBase64(bytes)
	dim DM,EL
	Set DM = CreateObject("Microsoft.XMLDOM")
	'Create temporary node with Base64 data type
	Set EL = DM.CreateElement("tmp")
	EL.DataType = "bin.base64"
	'Set bytes,get encoded String
	EL.NodeTypedValue = bytes
	encodeBase64 = EL.Text
	'MsgBox "encodeBase64 output's EL.Text vartype " & vartype(EL.Text)
End Function

Function decodeBase64(base64)
	dim DM,EL
	Set DM = CreateObject("Microsoft.XMLDOM")
	'Create temporary node with Base64 data type
	Set EL = DM.CreateElement("tmp")
	EL.DataType = "bin.base64"
	'Set encoded String, get bytes
	EL.Text = base64
	decodeBase64 = EL.NodeTypedValue
	'MsgBox "decodeBase64 output's EL.NodeTypedValue vartype " & vartype(EL.NodeTypedValue)
End Function

sub DPFPEnrollmentUserRegn_OnEnroll(finger, template, status)
    Dim fpe1
    Dim fpe2
    fpe1 = document.getElementById("fu_no1").value
    fpe2 = document.getElementById("fu_no2").value
    
    if fpe1 = "" Or fpe1 = null then
    	MsgBox "Finger print 1 successfully captured  "
    	document.getElementById("fputemplate1").value = encodeBase64(template.Serialize())
    	document.getElementById("fu_no1").value = finger
    else
    	MsgBox "Finger print 2 successfully captured  "
    	document.getElementById("fputemplate2").value = encodeBase64(template.Serialize())
    	document.getElementById("fu_no2").value = finger
    end if
end sub

sub DPFPEnrollmentUserRegn_OnDelete(delfp,status)
    Dim getfpd
    getfpd = delfp
    truncateUserFP(getfpd)
end sub