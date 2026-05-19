"use client"
import { useState, useRef, useEffect } from "react"
import api from "@/lib/api"
import { Bot, Send, User } from "lucide-react"

interface Message {
  role: "user" | "assistant"
  content: string
}

export default function AIPage() {
  const [messages, setMessages] = useState<Message[]>([])
  const [input, setInput] = useState("")
  const [isLoading, setIsLoading] = useState(false)
  const [provider, setProvider] = useState<"gemini" | "grok">("gemini")
  const endRef = useRef<HTMLDivElement>(null)

  useEffect(() => { endRef.current?.scrollIntoView({ behavior: "smooth" }) }, [messages])

  const sendMessage = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!input.trim()) return
    const userMsg: Message = { role: "user", content: input }
    setMessages((prev) => [...prev, userMsg])
    setInput("")
    setIsLoading(true)
    try {
      const { data } = await api.post("/ai/chat", { message: input, provider, conversation_history: messages })
      setMessages((prev) => [...prev, { role: "assistant", content: data.response }])
    } catch {
      setMessages((prev) => [...prev, { role: "assistant", content: "Sorry, I could not process your request." }])
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="flex flex-col h-[calc(100vh-10rem)]">
      <div className="flex items-center justify-between mb-4">
        <h1 className="text-2xl font-bold">AI Assistant</h1>
        <div className="flex gap-2">
          {(["gemini", "grok"] as const).map((p) => (
            <button key={p} onClick={() => setProvider(p)}
              className={`px-3 py-1 rounded-lg text-sm capitalize ${provider === p ? "bg-primary text-primary-foreground" : "bg-accent"}`}>
              {p}
            </button>
          ))}
        </div>
      </div>
      <div className="flex-1 overflow-y-auto space-y-4 p-4 bg-card rounded-xl border border-border">
        {messages.length === 0 && (
          <div className="flex flex-col items-center justify-center h-full text-muted-foreground space-y-2">
            <Bot className="w-12 h-12" />
            <p>Ask me about your finances, market analysis, or anything else</p>
          </div>
        )}
        {messages.map((msg, i) => (
          <div key={i} className={`flex gap-3 ${msg.role === "user" ? "justify-end" : ""}`}>
            {msg.role === "assistant" && <Bot className="w-6 h-6 text-primary shrink-0 mt-1" />}
            <div className={`max-w-[80%] p-3 rounded-lg ${msg.role === "user" ? "bg-primary text-primary-foreground" : "bg-accent"}`}>
              <p className="text-sm whitespace-pre-wrap">{msg.content}</p>
            </div>
            {msg.role === "user" && <User className="w-6 h-6 shrink-0 mt-1" />}
          </div>
        ))}
        {isLoading && (
          <div className="flex gap-3">
            <Bot className="w-6 h-6 text-primary shrink-0 mt-1" />
            <div className="p-3 rounded-lg bg-accent"><div className="flex gap-1"><span className="w-2 h-2 bg-muted-foreground rounded-full animate-bounce" /><span className="w-2 h-2 bg-muted-foreground rounded-full animate-bounce [animation-delay:0.1s]" /><span className="w-2 h-2 bg-muted-foreground rounded-full animate-bounce [animation-delay:0.2s]" /></div></div>
          </div>
        )}
        <div ref={endRef} />
      </div>
      <form onSubmit={sendMessage} className="mt-4 flex gap-2">
        <input type="text" value={input} onChange={(e) => setInput(e.target.value)} placeholder="Type a message..."
          className="flex-1 px-4 py-3 rounded-lg border border-input bg-background outline-none focus:ring-2 focus:ring-primary" />
        <button type="submit" disabled={isLoading}
          className="px-4 py-3 rounded-lg bg-primary text-primary-foreground disabled:opacity-50">
          <Send className="w-5 h-5" />
        </button>
      </form>
    </div>
  )
}
