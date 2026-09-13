file_css = 'D:/dsBilling/resources/css/app.css'
with open(file_css, 'a', encoding='utf-8') as f:
    f.write("""
/* Fix Chrome Autofill in Dark Mode */
.dark input:-webkit-autofill,
.dark input:-webkit-autofill:hover, 
.dark input:-webkit-autofill:focus, 
.dark input:-webkit-autofill:active {
    -webkit-box-shadow: 0 0 0 30px #0f172a inset !important;
    -webkit-text-fill-color: #f1f5f9 !important;
    transition: background-color 5000s ease-in-out 0s;
}
""")
print("CSS injected")
