import base64 as b64, os
files = os.listdir('../../notes/')
for i in files:
    read = open("../../notes/%s" % i)
    text = b64.b64decode(read.read()).decode('cp1252')
    out = open("../%s.dec.txt" % i.replace(".txt",""),'w')
    out.write(text)
    out.close()
exit(0)