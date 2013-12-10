--[[
Pop a pending item from one of the requsted channels, increase the number
of tries and register the item for the given worker.

KEYS: 
 - the channels (1..N-1)
 - worker_key (N)
]]--

-- retrieve & remove worker_key from KEYS
local worker_key = table.remove(KEYS)

for i, channel in ipairs(KEYS) do
    local task_key = redis.call('rpop', KEYS[i])

    if task_key then
        -- increase tries
        redis.call('hincrby', task_key, 'tries', 1)
        
        -- load task 
        local task = redis.call('hget', task_key, 'task')

        -- register that worker_key processes task
        redis.call('hmset', worker_key, 'current_task', task_key)

        return task
	end
end

return 0
