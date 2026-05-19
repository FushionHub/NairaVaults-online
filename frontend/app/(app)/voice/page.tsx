"use client"
import { useState } from "react"
import { useVoice } from "@/hooks/useVoice"
import api from "@/lib/api"
import { Mic, MicOff, Volume2, VolumeX } from "lucide-react"

export default function VoicePage() {
  const { isListening, isSpeaking, transcript, startListening, stopListening, speak, stopSpeaking } = useVoice()
  const [response, setResponse] = useState("")
  const [isProcessing, setIsProcessing] = useState(false)

  const handleVoiceQuery = async () => {
    if (!transcript.trim()) return
    setIsProcessing(true)
    try {
      const { data } = await api.post("/ai/chat", { message: transcript, provider: "gemini" })
      setResponse(data.response)
      await speak(data.response)
    } catch {
      setResponse("Sorry, I could not process your request.")
    } finally {
      setIsProcessing(false)
    }
  }

  return (
    <div className="max-w-2xl mx-auto space-y-8 text-center">
      <h1 className="text-2xl font-bold">Voice Assistant</h1>
      <div className="p-12 rounded-xl bg-card border border-border space-y-6">
        <div className={`w-24 h-24 mx-auto rounded-full flex items-center justify-center transition-all ${isListening ? "bg-red-500/20 animate-pulse" : "bg-accent"}`}>
          {isListening ? <Mic className="w-10 h-10 text-red-500" /> : <MicOff className="w-10 h-10 text-muted-foreground" />}
        </div>
        <div className="flex justify-center gap-4">
          <button onClick={isListening ? stopListening : startListening}
            className={`px-6 py-3 rounded-lg font-medium ${isListening ? "bg-red-500 text-white" : "bg-primary text-primary-foreground"}`}>
            {isListening ? "Stop" : "Start Listening"}
          </button>
          {transcript && !isListening && (
            <button onClick={handleVoiceQuery} disabled={isProcessing}
              className="px-6 py-3 rounded-lg bg-accent font-medium disabled:opacity-50">
              {isProcessing ? "Processing..." : "Send"}
            </button>
          )}
          {isSpeaking && (
            <button onClick={stopSpeaking} className="px-6 py-3 rounded-lg bg-accent">
              <VolumeX className="w-5 h-5" />
            </button>
          )}
        </div>
        {transcript && (
          <div className="p-4 rounded-lg bg-accent text-left">
            <p className="text-xs text-muted-foreground mb-1">You said:</p>
            <p>{transcript}</p>
          </div>
        )}
        {response && (
          <div className="p-4 rounded-lg bg-primary/10 text-left">
            <div className="flex items-center gap-2 mb-1">
              <Volume2 className="w-4 h-4 text-primary" />
              <p className="text-xs text-muted-foreground">AI Response:</p>
            </div>
            <p className="text-sm">{response}</p>
          </div>
        )}
      </div>
    </div>
  )
}
