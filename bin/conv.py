import base64 as b64, os

searchDir, exportDir, fileExt = '../notes/', '../exported_notes/', '.txt'
files = os.listdir(searchDir)
for i in files:
    read = open("%s%s" % (searchDir, i))
    text = b64.b64decode(read.read()).decode('cp1252')
    out = open("%s%s.dec.txt" %  ('../exported_notes/', i.replace(fileExt,"")) ,'w')
    out.write(text)
    out.close()
